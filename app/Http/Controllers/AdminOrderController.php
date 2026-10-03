<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    /**
     * Hiển thị danh sách toàn bộ đơn hàng dành cho Admin
     */
    public function index(Request $request)
    {
        $query = Order::with(['items', 'messages.sender', 'chatMessages.sender', 'paymentTransactions'])->latest();

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('id', 'like', "%{$keyword}%")
                  ->orWhere('customer_name', 'like', "%{$keyword}%")
                  ->orWhere('customer_phone', 'like', "%{$keyword}%")
                  ->orWhere('ghn_order_code', 'like', "%{$keyword}%")
                  ->orWhereHas('messages', function ($messageQuery) use ($keyword) {
                      $messageQuery->where('message', 'like', "%{$keyword}%");
                  })
                  ->orWhereHas('chatMessages', function ($messageQuery) use ($keyword) {
                      $messageQuery->where('message', 'like', "%{$keyword}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $normalized = Order::normalizeStatus($request->status);
            $query->where('status', $normalized);
        }

        $orders = $query->get();

        $orders->each(fn (Order $order) => $this->mergeOrderMessages($order));

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items', 'messages.sender', 'chatMessages.sender', 'paymentTransactions']);
        $this->mergeOrderMessages($order);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Cập nhật trạng thái đơn hàng theo flow mới.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,pending_confirmation,awaiting_pickup,awaiting_delivery,in_transit,completed,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $nextStatus = Order::normalizeStatus($request->status);

        if ($order->status === Order::STATUS_CANCELLED && $nextStatus !== Order::STATUS_CANCELLED) {
            return redirect()->back()->withErrors([
                'status' => 'Đơn đã hủy là trạng thái cuối, không thể chuyển lại sang trạng thái giao hàng.',
            ]);
        }

        if ($order->status === Order::STATUS_AWAITING_DELIVERY && $nextStatus === Order::STATUS_CANCELLED) {
            return redirect()->back()->withErrors([
                'status' => 'Đơn hàng đang ở trạng thái chờ giao hàng, không được hủy.',
            ]);
        }

        if ($order->status === Order::STATUS_IN_TRANSIT && $nextStatus === Order::STATUS_CANCELLED) {
            return redirect()->back()->withErrors([
                'status' => 'Đơn hàng đang giao, không được hủy.',
            ]);
        }

        $order->update([
            'status' => $nextStatus,
        ]);

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái đơn hàng thành công!');
    }

    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|min:1|max:1000',
        ]);

        $order = Order::findOrFail($id);
        $order->messages()->create([
            'sender_id' => auth()->id(),
            'sender_role' => auth()->user()->role,
            'message' => trim($request->message),
        ]);

        return redirect()->back()->with('success', 'Đã gửi tin nhắn cho khách hàng.');
    }

    private function mergeOrderMessages(Order $order): void
    {
        $messages = $order->messages->concat($order->chatMessages)->sortBy('created_at')->values();

        $messages->each(function ($message) {
            if ($message->sender_role === 'staff') {
                $staffName = $message->sender?->name ?? 'Nhân viên';
                $message->message = $staffName . ': ' . $message->message;
                $message->sender_role = 'admin';
            }
        });

        $order->setRelation('messages', $messages);
    }
}