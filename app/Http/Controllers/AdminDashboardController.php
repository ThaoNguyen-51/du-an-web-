<?php

namespace App\Http\Controllers;

use App\Models\AirConditioner;
use App\Models\AirConditionerVariant;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $paidOrders = $this->paidOrders();
        $today = now()->startOfDay();
        $todayOrders = $paidOrders->filter(fn (Order $order) => $order->created_at?->greaterThanOrEqualTo($today));

        return view('admin.dashboard', [
            'totalRevenue' => $paidOrders->sum('total_amount'),
            'todayRevenue' => $todayOrders->sum('total_amount'),
            'totalOrders' => Order::count(),
            'pendingOrders' => Order::where('status', Order::STATUS_PENDING_CONFIRMATION)->count(),
            'totalProducts' => AirConditioner::count(),
            'totalCustomers' => DB::table('users')->where('role', 'user')->count(),
            'lowStockCount' => AirConditionerVariant::whereBetween('stock', [1, 5])->count(),
            'outOfStockCount' => AirConditionerVariant::where('stock', '<=', 0)->count(),
            'recentOrders' => Order::with('user')->latest()->limit(6)->get(),
            'lowStockVariants' => AirConditionerVariant::with('airConditioner')
                ->where('stock', '<=', 5)
                ->orderBy('stock')
                ->limit(6)
                ->get(),
        ]);
    }

    private function paidOrders(): Collection
    {
        return Order::with('paymentTransactions')
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->get()
            ->filter(function (Order $order): bool {
                $hasPaidTransaction = $order->paymentTransactions
                    ->contains(fn ($transaction): bool => $transaction->status === 'paid');

                return $hasPaidTransaction
                    || (($order->payment_method ?? 'cod') === 'cod'
                        && $order->status === Order::STATUS_COMPLETED);
            })
            ->values();
    }
}
