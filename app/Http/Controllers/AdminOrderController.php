<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\AdminActivityLogger;

class AdminOrderController extends Controller
{
    /**
     * Hiển thị danh sách toàn bộ đơn hàng dành cho Admin
     */
    public function index(Request $request)
    {
        $query = $this->orderQuery($request);
        $orders = $query->get();
        $orders->each(fn (Order $order) => $this->mergeOrderMessages($order));
        $exportOrders = Order::query()
            ->select(['id', 'customer_name', 'created_at'])
            ->latest()
            ->get();
        $pendingOrderCount = Order::where('status', Order::STATUS_PENDING_CONFIRMATION)->count();

        return view('admin.orders.index', compact('orders', 'exportOrders', 'pendingOrderCount'));
    }

    private function orderQuery(Request $request)
    {
        $query = Order::with(['items', 'messages.sender', 'chatMessages.sender', 'paymentTransactions']);

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('id', 'like', "%{$keyword}%")
                  ->orWhere('customer_name', 'like', "%{$keyword}%")
                  ->orWhere('customer_phone', 'like', "%{$keyword}%")
                  ->orWhere('ghn_order_code', 'like', "%{$keyword}%")
                  ->orWhere('virtual_tracking_code', 'like', "%{$keyword}%")
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

        if ($request->filled('payment_method') && in_array($request->payment_method, ['cod', 'visa', 'domestic'], true)) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('payment_status') && in_array($request->payment_status, ['paid', 'pending'], true)) {
            if ($request->payment_status === 'paid') {
                $query->whereHas('paymentTransactions', fn ($transactionQuery) => $transactionQuery->where('status', 'paid'));
            } else {
                $query->whereDoesntHave('paymentTransactions', fn ($transactionQuery) => $transactionQuery->where('status', 'paid'));
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $request->input('sort') === 'oldest'
            ? $query->oldest('created_at')
            : $query->latest('created_at');

        return $query;
    }

    public function show(Order $order)
    {
        $order->load(['items', 'messages.sender', 'chatMessages.sender', 'paymentTransactions', 'statusHistories']);
        $this->mergeOrderMessages($order);

        return view('admin.orders.show', compact('order'));
    }

    public function confirm(Order $order)
    {
        if (Order::normalizeStatus($order->status) !== Order::STATUS_PENDING_CONFIRMATION) {
            return redirect()->back()->withErrors([
                'status' => 'Chỉ có thể xác nhận đơn đang chờ xác nhận.',
            ]);
        }

        return $this->changeStatus($order, Order::STATUS_AWAITING_PICKUP, 'Đã xác nhận đơn hàng.');
    }

    public function exportCsv(Request $request)
    {
        $request->validate([
            'export_scope' => ['required', 'in:date_range,order'],
            'date_from' => ['required_if:export_scope,date_range', 'nullable', 'date'],
            'date_to' => ['required_if:export_scope,date_range', 'nullable', 'date', 'after_or_equal:date_from'],
            'order_id' => ['required_if:export_scope,order', 'nullable', 'integer', 'exists:orders,id'],
        ], [
            'date_from.required_if' => 'Vui lòng chọn ngày bắt đầu.',
            'date_to.required_if' => 'Vui lòng chọn ngày kết thúc.',
            'date_to.after_or_equal' => 'Ngày kết thúc phải từ ngày bắt đầu trở đi.',
            'order_id.required_if' => 'Vui lòng chọn đơn hàng cần xuất.',
        ]);

        $ordersQuery = Order::with(['paymentTransactions'])->latest();
        if ($request->export_scope === 'order') {
            $ordersQuery->whereKey($request->integer('order_id'));
        } else {
            $ordersQuery->whereDate('created_at', '>=', $request->date_from)
                ->whereDate('created_at', '<=', $request->date_to);
        }
        $orders = $ordersQuery->get();
        $filename = 'don-hang-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($orders) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Mã đơn', 'Ngày đặt', 'Khách hàng', 'Điện thoại', 'Thanh toán', 'Trạng thái', 'Tổng tiền'], ';');
            foreach ($orders as $order) {
                fputcsv($output, [
                    $order->order_code ?: '#' . $order->id,
                    optional($order->created_at)->format('d/m/Y H:i'),
                    $order->customer_name,
                    $order->customer_phone,
                    strtoupper($order->payment_method ?: 'N/A'),
                    $order->status_label,
                    number_format((float) $order->total_amount, 2, '.', ''),
                ], ';');
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
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
        return $this->changeStatus($order, $nextStatus);
    }

    private function changeStatus(Order $order, string $nextStatus, ?string $note = null)
    {
        $nextStatus = Order::normalizeStatus($nextStatus);

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

        $oldStatus = Order::normalizeStatus($order->status);
        if ($oldStatus !== $nextStatus) {
            $order->update(['status' => $nextStatus]);
            $order->statusHistories()->create([
                'from_status' => $oldStatus,
                'to_status' => $nextStatus,
                'changed_by' => auth()->id(),
                'note' => $note,
            ]);
            app(AdminActivityLogger::class)->record('order_status_changed', "Cập nhật trạng thái đơn #{$order->id}", $order, [
                'from' => $oldStatus, 'to' => $nextStatus,
            ]);
        }

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