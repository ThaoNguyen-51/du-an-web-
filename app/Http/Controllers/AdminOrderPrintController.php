<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderPrintHistory;
use App\Services\AdminActivityLogger;
use Illuminate\Http\Request;

class AdminOrderPrintController extends Controller
{
    public function index(Request $request)
    {
        $orders = $this->filteredOrders($request)->get();

        return view('admin.orders.print.index', [
            'orders' => $orders,
            'statusOptions' => Order::statusOptions(),
        ]);
    }

    public function printOne(Order $order)
    {
        $order->load(['items', 'paymentTransactions', 'statusHistories']);
        $this->recordPrint($order, 'single');

        return view('admin.orders.print.page', [
            'orders' => collect([$order]),
            'printType' => 'single',
        ]);
    }

    public function printBulk(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer', 'distinct', 'exists:orders,id'],
        ]);

        $orderIds = collect($validated['order_ids'])->map(fn ($id) => (int) $id)->values()->all();
        $orders = Order::with(['items', 'paymentTransactions', 'statusHistories'])
            ->whereIn('id', $orderIds)
            ->get()
            ->sortBy(fn (Order $order) => array_search($order->id, $orderIds, true))
            ->values();

        foreach ($orders as $order) {
            $this->recordPrint($order, 'bulk');
        }

        return view('admin.orders.print.page', [
            'orders' => $orders,
            'printType' => 'bulk',
        ]);
    }

    private function filteredOrders(Request $request)
    {
        $query = Order::with(['items', 'paymentTransactions', 'printHistories'])->latest();

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('id', 'like', "%{$keyword}%")
                    ->orWhere('customer_name', 'like', "%{$keyword}%")
                    ->orWhere('customer_phone', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status') && array_key_exists($request->status, Order::statusOptions())) {
            $query->where('status', Order::normalizeStatus($request->status));
        }

        if ($request->filled('payment_method') && in_array($request->payment_method, ['cod', 'visa', 'domestic'], true)) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('payment_status') && in_array($request->payment_status, ['paid', 'pending'], true)) {
            if ($request->payment_status === 'paid') {
                $query->whereHas('paymentTransactions', fn ($q) => $q->where('status', 'paid'));
            } else {
                $query->whereDoesntHave('paymentTransactions', fn ($q) => $q->where('status', 'paid'));
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->input('printed') === 'printed') {
            $query->whereHas('printHistories');
        } elseif ($request->input('printed') === 'unprinted') {
            $query->whereDoesntHave('printHistories');
        }

        if ($request->input('sort') === 'oldest') {
            $query->oldest();
        }

        return $query;
    }

    private function recordPrint(Order $order, string $type): void
    {
        OrderPrintHistory::create([
            'order_id' => $order->id,
            'printed_by' => auth()->id(),
            'print_type' => $type,
            'printed_at' => now(),
            'ip_address' => request()->ip(),
        ]);

        app(AdminActivityLogger::class)->record(
            'order_printed',
            "In đơn #{$order->id}",
            $order,
            ['print_type' => $type]
        );
    }
}
