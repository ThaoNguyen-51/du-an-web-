<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class MomoService
{
    public function createPayment(Order $order, PaymentTransaction $transaction): array
    {
        $accessKey = (string) config('services.momo.access_key');
        $secretKey = (string) config('services.momo.secret_key');
        $amount = (string) ((int) $order->total_amount);
        $orderId = $order->id . '_' . $transaction->id . '_' . time();
        $requestId = (string) $transaction->id . '_' . time();
        $orderInfo = 'Thanh toan don hang #' . $order->id;
        $redirectUrl = config('services.momo.redirect_url') ?: route('payment.momo.callback');
        $ipnUrl = config('services.momo.ipn_url') ?: route('payment.momo.ipn');
        $extraData = (string) $order->id;
        $requestType = match ($order->payment_method) {
            'momo_qr' => 'captureWallet',
            'domestic' => 'payWithATM',
            'visa' => 'payWithCC',
            default => 'payWithCC',
        };

        $rawHash = 'accessKey=' . $accessKey . '&amount=' . $amount . '&extraData=' . $extraData
            . '&ipnUrl=' . $ipnUrl . '&orderId=' . $orderId . '&orderInfo=' . $orderInfo
            . '&partnerCode=' . config('services.momo.partner_code') . '&redirectUrl=' . $redirectUrl
            . '&requestId=' . $requestId . '&requestType=' . $requestType;

        $data = [
            'partnerCode' => config('services.momo.partner_code'),
            'partnerName' => config('app.name'),
            'storeId' => 'AirConditionerStore',
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => hash_hmac('sha256', $rawHash, $secretKey),
        ];

        $transaction->update(['gateway_order_id' => $orderId, 'request_payload' => $data]);

        try {
            $response = Http::withOptions([
                'verify' => (bool) config('services.momo.verify_ssl', true),
            ])->timeout(20)->post(config('services.momo.endpoint'), $data);
            $result = $response->json() ?? [];
        } catch (\Throwable $exception) {
            $result = ['resultCode' => -1, 'message' => 'Không thể kết nối tới MoMo.'];
        }

        $transaction->update([
            'response_payload' => $result,
            'result_code' => isset($result['resultCode']) ? (int) $result['resultCode'] : null,
            'message' => $result['message'] ?? null,
            'status' => isset($result['payUrl']) ? 'initiated' : 'failed',
        ]);

        return $result;
    }

    public function isSuccessful(array $payload): bool
    {
        return (string) ($payload['resultCode'] ?? '') === '0';
    }

    public function markPaid(PaymentTransaction $transaction, array $payload): void
    {
        $transaction->update([
            'transaction_id' => $payload['transId'] ?? null,
            'result_code' => (int) ($payload['resultCode'] ?? 0),
            'message' => $payload['message'] ?? null,
            'response_payload' => $payload,
            'status' => 'paid',
            'paid_at' => Carbon::now(),
        ]);
    }

    public function markFailed(PaymentTransaction $transaction, array $payload): void
    {
        $transaction->update([
            'transaction_id' => $payload['transId'] ?? null,
            'result_code' => isset($payload['resultCode']) ? (int) $payload['resultCode'] : null,
            'message' => $payload['message'] ?? null,
            'response_payload' => $payload,
            'status' => 'failed',
        ]);
    }

    public function isValidSuccessfulResponse(array $payload): bool
    {
        return $this->isValidResponse($payload) && $this->isSuccessful($payload);
    }

    public function isValidResponse(array $payload): bool
    {
        if (!isset($payload['signature'])) {
            return false;
        }

        $rawHash = 'accessKey=' . config('services.momo.access_key')
            . '&amount=' . ($payload['amount'] ?? '')
            . '&extraData=' . ($payload['extraData'] ?? '')
            . '&message=' . ($payload['message'] ?? '')
            . '&orderId=' . ($payload['orderId'] ?? '')
            . '&orderInfo=' . ($payload['orderInfo'] ?? '')
            . '&orderType=' . ($payload['orderType'] ?? '')
            . '&partnerCode=' . ($payload['partnerCode'] ?? '')
            . '&payType=' . ($payload['payType'] ?? '')
            . '&requestId=' . ($payload['requestId'] ?? '')
            . '&responseTime=' . ($payload['responseTime'] ?? '')
            . '&resultCode=' . ($payload['resultCode'] ?? '')
            . '&transId=' . ($payload['transId'] ?? '');

        return hash_equals(hash_hmac('sha256', $rawHash, (string) config('services.momo.secret_key')), (string) $payload['signature']);
    }
}