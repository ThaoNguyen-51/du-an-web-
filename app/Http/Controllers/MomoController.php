<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\MomoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MomoController extends Controller
{
    public function start(Order $order, MomoService $momo)
    {
        $this->authorizeOrder($order);
        abort_unless(Order::isOnlinePaymentMethod($order->payment_method), 404);
        if ($order->paymentTransactions()->where('status', 'paid')->exists() || $order->ghn_order_code) {
            return redirect()->route('user.orders.show', $order)->with('error', 'Đơn hàng này đã thanh toán.');
        }

        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'momo',
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]);

        return $this->redirectToMomo($order, $transaction, $momo);
    }

    public function payAgain(Order $order, MomoService $momo)
    {
        $this->authorizeOrder($order);
        abort_unless(Order::isOnlinePaymentMethod($order->payment_method), 404);

        if ($order->paymentTransactions()->where('status', 'paid')->exists() || $order->ghn_order_code) {
            return redirect()->route('user.orders.show', $order)->with('error', 'Đơn hàng này đã thanh toán.');
        }

        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'momo',
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]);

        return $this->redirectToMomo($order, $transaction, $momo);
    }

    public function callback(Request $request, MomoService $momo)
    {
        if (!$momo->isValidResponse($request->all())) {
            return redirect()->route('user.orders.index')->with('error', 'Phản hồi MoMo không hợp lệ.');
        }

        if (!$momo->isSuccessful($request->all())) {
            $this->markFailed($request->all(), $momo);
            return redirect()->route('user.orders.index')->with(
                'error',
                'Thanh toán chưa thành công: ' . ($request->input('message') ?: 'MoMo từ chối giao dịch.') . ' Bạn có thể thanh toán lại.'
            );
        }

        $result = $this->completePayment($request->all(), $momo);
        if ($result === 'paid' || $result === 'already_paid') {
            $request->session()->forget('cart');
            return redirect()->route('user.orders.index')->with('success', 'Thanh toán thẻ thành công.');
        }

        if ($result === 'failed') {
            $this->markFailed($request->all(), $momo);
        }

        return redirect()->route('user.orders.index')->with(
            'error',
            'Thanh toán chưa được ghi nhận. Bạn có thể thanh toán lại.'
        );
    }

    public function ipn(Request $request, MomoService $momo)
    {
        if ($momo->isValidSuccessfulResponse($request->all())) {
            $this->completePayment($request->all(), $momo);
        } elseif ($momo->isValidResponse($request->all())) {
            $this->markFailed($request->all(), $momo);
        }

        return response()->json(['message' => 'Received']);
    }

    private function redirectToMomo(Order $order, PaymentTransaction $transaction, MomoService $momo)
    {
        $result = $momo->createPayment($order, $transaction);

        if ($order->payment_method === 'momo_qr' && !empty($result['qrCodeUrl'])) {
            return view('shop.payment.momo_qr', [
                'order' => $order,
                'qrCodeUrl' => $result['qrCodeUrl'],
                'payUrl' => $result['payUrl'] ?? null,
            ]);
        }

        return !empty($result['payUrl'])
            ? redirect($result['payUrl'])
            : redirect()->route('user.orders.show', $order)->with(
                'error',
                $result['message'] ?? 'MoMo không tạo được liên kết thanh toán.'
            );
    }

    private function completePayment(array $payload, MomoService $momo): string
    {
        $result = DB::transaction(function () use ($payload, $momo) {
            $transaction = PaymentTransaction::whereIn('gateway', ['momo', 'visa'])
                ->where('gateway_order_id', $payload['orderId'] ?? '')
                ->lockForUpdate()->first();

            if (!$transaction) {
                return 'invalid';
            }

            $order = Order::lockForUpdate()->find($transaction->order_id);
            if (!$order || (int) $transaction->amount !== (int) ($payload['amount'] ?? 0)) {
                if ($transaction->status !== 'paid') {
                    $momo->markFailed($transaction, $payload);
                }
                return 'invalid';
            }

            if ($transaction->status === 'paid') {
                return 'already_paid';
            }

            // MoMo payment state belongs to the transaction; the order still awaits shop confirmation.
            $order->update(['shipping_status' => 'not_shipped']);
            $momo->markPaid($transaction, $payload);
            return ['paid', $order->id];
        });

        if (!is_array($result)) {
            return $result;
        }

        return 'paid';
    }

    private function markFailed(array $payload, MomoService $momo): void
    {
        $transaction = PaymentTransaction::whereIn('gateway', ['momo', 'visa'])
            ->where('gateway_order_id', $payload['orderId'] ?? '')
            ->first();

        if ($transaction && $transaction->status !== 'paid') {
            $momo->markFailed($transaction, $payload);
        }
    }

    private function authorizeOrder(Order $order): void
    {
        abort_unless($order->user_id === Auth::id(), 403);
    }
}