<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_reports_with_paid_order_revenue(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $order = Order::factory()->create([
            'status' => Order::STATUS_COMPLETED,
            'payment_method' => 'visa',
            'total_amount' => 8000000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_name' => 'Điều hòa Casper Inverter 9000 BTU',
            'capacity_name' => '9.000 BTU',
            'price' => 8000000,
            'quantity' => 1,
            'subtotal' => 8000000,
        ]);

        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'visa',
            'gateway_order_id' => 'report-test-' . $order->id,
            'amount' => 8000000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('8.000.000')
            ->assertSee('Điều hòa Casper Inverter 9000 BTU');

        $this->actingAs($admin)
            ->get(route('admin.reports.charts'))
            ->assertOk()
            ->assertSee('productChart');
    }

    public function test_finance_method_summary_only_shows_supported_methods_with_orders(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        Order::factory()->create([
            'payment_method' => 'cod',
            'status' => Order::STATUS_COMPLETED,
        ]);
        $qrOrder = Order::factory()->create([
            'payment_method' => 'momo_qr',
            'status' => Order::STATUS_COMPLETED,
        ]);
        $bankOrder = Order::factory()->create([
            'payment_method' => 'bank',
            'status' => Order::STATUS_COMPLETED,
        ]);
        foreach ([[$qrOrder, 'momo_qr'], [$bankOrder, 'bank']] as [$order, $gateway]) {
            PaymentTransaction::create([
                'order_id' => $order->id,
                'gateway' => $gateway,
                'gateway_order_id' => 'unsupported-' . $order->id,
                'amount' => $order->total_amount,
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.finance.index'))
            ->assertOk()
            ->assertSee('COD')
            ->assertDontSee('QR MoMo')
            ->assertDontSee('Chuyển khoản')
            ->assertDontSee('Chưa xác định')
            ->assertDontSee('Visa</td>')
            ->assertDontSee('Thẻ nội địa</td>');
    }
}
