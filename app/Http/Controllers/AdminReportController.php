<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function index()
    {
        $paidOrders = $this->paidOrders();
        $dailyRevenue = $this->revenueByPeriod($paidOrders, 'day');
        $monthlyRevenue = $this->revenueByPeriod($paidOrders, 'month');
        $yearlyRevenue = $this->revenueByPeriod($paidOrders, 'year');

        return view('admin.reports.index', [
            'totalOrders' => Order::count(),
            'totalCustomers' => DB::table('users')->where('role', 'user')->count(),
            'totalRevenue' => $paidOrders->sum('total_amount'),
            'productRevenue' => $this->productRevenue($paidOrders),
            'dailyRevenue' => $dailyRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'yearlyRevenue' => $yearlyRevenue,
            'paymentRevenue' => $this->paymentRevenue($paidOrders),
        ]);
    }

    public function charts()
    {
        $paidOrders = $this->paidOrders();
        $productRevenue = $this->productRevenue($paidOrders);
        $dailyRevenue = $this->revenueByPeriod($paidOrders, 'day');
        $monthlyRevenue = $this->revenueByPeriod($paidOrders, 'month');
        $yearlyRevenue = $this->revenueByPeriod($paidOrders, 'year');
        $paymentRevenue = $this->paymentRevenue($paidOrders);

        return view('admin.reports.charts', compact(
            'productRevenue',
            'dailyRevenue',
            'monthlyRevenue',
            'yearlyRevenue',
            'paymentRevenue'
        ));
    }

    private function paidOrders(): Collection
    {
        return Order::with(['items', 'paymentTransactions'])
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->get()
            ->filter(function (Order $order): bool {
                $hasPaidTransaction = $order->paymentTransactions
                    ->contains(fn ($transaction): bool => $transaction->status === 'paid');

                $isCompletedCod = ($order->payment_method ?? 'cod') === 'cod'
                    && $order->status === Order::STATUS_COMPLETED;

                return $hasPaidTransaction || $isCompletedCod;
            })
            ->values();
    }

    private function productRevenue(Collection $orders): Collection
    {
        return $orders->flatMap(function (Order $order): Collection {
            return $order->items->map(function ($item) use ($order): object {
                $subtotal = (float) ($item->subtotal ?? ((float) $item->price * $item->quantity));

                return (object) [
                    'product_name' => $item->product_name ?: 'Sản phẩm không xác định',
                    'quantity' => (int) $item->quantity,
                    'revenue' => $subtotal,
                    'order_id' => $order->id,
                ];
            });
        })->groupBy('product_name')->map(function (Collection $items, string $productName): object {
            return (object) [
                'product_name' => $productName,
                'quantity' => $items->sum('quantity'),
                'revenue' => $items->sum('revenue'),
            ];
        })->sortByDesc('revenue')->values();
    }

    private function revenueByPeriod(Collection $orders, string $period): Collection
    {
        return $orders->groupBy(function (Order $order) use ($period): string {
            return match ($period) {
                'year' => $order->created_at->format('Y'),
                'month' => $order->created_at->format('Y-m'),
                default => $order->created_at->format('Y-m-d'),
            };
        })->map(function (Collection $periodOrders, string $label): object {
            return (object) [
                'label' => $label,
                'order_count' => $periodOrders->count(),
                'revenue' => $periodOrders->sum('total_amount'),
            ];
        })->sortBy('label')->values();
    }

    private function paymentRevenue(Collection $orders): Collection
    {
        return $orders->groupBy(function (Order $order): string {
            $transaction = $order->paymentTransactions
                ->where('status', 'paid')
                ->sortByDesc('id')
                ->first();

            return $order->payment_method ?? ($transaction?->gateway ?? 'cod');
        })->map(function (Collection $methodOrders, string $method): object {
            return (object) [
                'method' => match ($method) {
                    'visa' => 'Visa',
                    'domestic' => 'Thẻ nội địa',
                    'cod' => 'COD',
                    default => $method,
                },
                'revenue' => $methodOrders->sum('total_amount'),
            ];
        })->values();
    }
}
