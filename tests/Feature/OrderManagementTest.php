<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AirConditioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_order_from_cart_with_valid_item_data(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $product = AirConditioner::create([
            'name' => 'Điều hòa Casper',
            'brand' => 'Casper',
            'price' => 12000000,
        ]);

        session()->put('cart', [
            '1_default' => [
                'product_id' => $product->id,
                'variant_id' => null,
                'name' => 'Điều hòa Casper',
                'brand' => 'Casper',
                'capacity' => '9000 BTU',
                'price' => 12000000,
                'quantity' => 2,
                'image' => 'demo.jpg',
            ],
        ]);

        $response = $this->actingAs($user)
            ->from('/cart')
            ->post('/orders', [
                'customer_name' => 'Nguyễn Văn A',
                'customer_phone' => '0909123456',
                'customer_address' => '123 Lê Lợi, Hà Nội',
                'payment_method' => 'cod',
                'selected_items' => ['1_default'],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'customer_name' => 'Nguyễn Văn A',
            'payment_method' => 'cod',
        ]);
        $this->assertDatabaseHas('order_items', [
            'air_conditioner_id' => $product->id,
            'product_name' => 'Điều hòa Casper',
            'capacity_name' => '9000 BTU',
            'quantity' => 2,
        ]);

        $order = \App\Models\Order::where('user_id', $user->id)->firstOrFail();
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{6}$/', $order->virtual_tracking_code);

        $this->actingAs($user)
            ->get(route('user.orders.index'))
            ->assertOk()
            ->assertSee($order->virtual_tracking_code)
            ->assertDontSee('Mã nội bộ:')
            ->assertDontSee('Đơn ảo, không giao hàng');
    }

    public function test_admin_order_index_filters_by_status_and_keyword(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/admin/orders?status=pending&keyword=Test')
            ->assertOk();
    }

    public function test_customer_can_search_orders_by_keyword(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $order = \App\Models\Order::factory()->create([
            'user_id' => $user->id,
            'customer_name' => 'Nguyễn Thị Hương',
            'ghn_order_code' => 'GHN-224466',
            'total_amount' => 2500000,
        ]);

        $this->actingAs($user)
            ->get('/orders?keyword=GHN-224466')
            ->assertOk()
            ->assertSee('GHN-224466')
            ->assertSee('#' . $order->id);

        $this->actingAs($user)
            ->get('/orders?keyword=Hương')
            ->assertOk()
            ->assertSee('#' . $order->id);
    }

    public function test_admin_can_view_order_detail_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $order = \App\Models\Order::factory()->create([
            'customer_name' => 'Khách xem chi tiết',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Chi tiết đơn hàng #' . $order->id)
            ->assertSee($order->virtual_tracking_code)
            ->assertDontSee('Mã vận đơn nội bộ')
            ->assertSee('Khách xem chi tiết')
            ->assertSee('Phản hồi khách hàng');
    }

    public function test_admin_can_search_orders_by_customer_name_tracking_code_and_message(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $order = \App\Models\Order::factory()->create([
            'customer_name' => 'Nguyễn Thị Lan',
            'customer_phone' => '0909000111',
            'ghn_order_code' => 'GHN-998877',
        ]);

        $order->messages()->create([
            'sender_id' => $admin->id,
            'sender_role' => 'admin',
            'message' => 'Khách đã yêu cầu kiểm tra hàng.',
        ]);

        $this->actingAs($admin)
            ->get('/admin/orders?keyword=Lan')
            ->assertOk()
            ->assertSee('Nguyễn Thị Lan');

        $this->actingAs($admin)
            ->get('/admin/orders?keyword=GHN-998877')
            ->assertOk()
            ->assertSee('Nguyễn Thị Lan');

        $this->actingAs($admin)
            ->get('/admin/orders?keyword=kiểm tra hàng')
            ->assertOk()
            ->assertSee('Nguyễn Thị Lan');
    }

    public function test_customer_and_admin_can_send_messages_for_order(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $order = \App\Models\Order::factory()->create([
            'user_id' => $user->id,
            'customer_name' => 'Nguyễn Văn A',
            'customer_phone' => '0909123456',
            'customer_address' => '123 Lê Lợi, Hà Nội',
            'status' => 'pending_confirmation',
            'total_amount' => 2500000,
        ]);

        $this->actingAs($user)
            ->post('/orders/' . $order->id . '/messages', [
                'message' => 'Xin chào, tôi muốn hỏi về đơn hàng.',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post('/admin/orders/' . $order->id . '/messages', [
                'message' => 'Chúng tôi đã xác nhận đơn hàng của bạn.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('order_messages', [
            'order_id' => $order->id,
            'message' => 'Xin chào, tôi muốn hỏi về đơn hàng.',
        ]);

        $this->assertDatabaseHas('order_messages', [
            'order_id' => $order->id,
            'message' => 'Chúng tôi đã xác nhận đơn hàng của bạn.',
        ]);
    }

    public function test_customer_can_start_direct_chat_and_admin_can_search_and_reply(): void
    {
        $user = User::factory()->create([
            'name' => 'Khách Chat Riêng',
            'email_verified_at' => now(),
        ]);
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('chat.index'))
            ->assertOk()
            ->assertSee('Bắt đầu trò chuyện với shop')
            ->assertDontSee('© 2026 HC Electric. All rights reserved.');

        $this->actingAs($user)
            ->post(route('chat.send'), [
                'message' => 'Tôi cần tư vấn trực tiếp.',
            ])
            ->assertRedirect(route('chat.index'));

        $this->assertDatabaseHas('chat_messages', [
            'sender_id' => $user->id,
            'recipient_id' => $admin->id,
            'order_id' => null,
            'message' => 'Tôi cần tư vấn trực tiếp.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.chat.index', ['keyword' => 'tư vấn trực tiếp']))
            ->assertOk()
            ->assertSee('Khách Chat Riêng')
            ->assertSee('Tôi cần tư vấn trực tiếp.')
            ->assertSee('admin-chat-shell', false)
            ->assertSee('chat-info-panel', false)
            ->assertSee('Mẫu trả lời nhanh');

        $this->actingAs($admin)
            ->post(route('admin.chat.send'), [
                'customer_id' => $user->id,
                'message' => 'Shop sẵn sàng hỗ trợ bạn.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('chat_messages', [
            'sender_id' => $admin->id,
            'recipient_id' => $user->id,
            'message' => 'Shop sẵn sàng hỗ trợ bạn.',
        ]);
    }

    public function test_chat_message_can_be_linked_to_customer_order(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $order = \App\Models\Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 2500000,
        ]);
        $product = \App\Models\AirConditioner::create([
            'name' => 'Điều hòa thử chat',
            'brand' => 'HC',
            'price' => 8500000,
        ]);
        \App\Models\ChatMessage::create([
            'sender_id' => $user->id,
            'recipient_id' => $admin->id,
            'sender_role' => 'customer',
            'message' => 'Tin nhắn chung trước khi chọn đơn.',
        ]);

        $this->actingAs($user)
            ->post(route('chat.send'), [
                'order_id' => $order->id,
                'product_id' => $product->id,
                'message' => 'Tôi muốn hỏi về đơn này.',
            ])
            ->assertRedirect(route('chat.index', ['order_id' => $order->id, 'product_id' => $product->id]));

        $this->assertDatabaseHas('chat_messages', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'sender_id' => $user->id,
            'recipient_id' => $admin->id,
            'message' => 'Tôi muốn hỏi về đơn này.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.chat.index', ['customer_id' => $user->id, 'order_id' => $order->id, 'product_id' => $product->id]))
            ->assertOk()
            ->assertSee('Tôi muốn hỏi về đơn này.')
            ->assertSee('Tin nhắn chung trước khi chọn đơn.')
            ->assertSee('Điều hòa thử chat');

        $this->actingAs($user)
            ->get(route('chat.index', ['order_id' => $order->id, 'product_id' => $product->id]))
            ->assertOk()
            ->assertSee('Tôi muốn hỏi về đơn này.')
            ->assertSee('Tin nhắn chung trước khi chọn đơn.');
    }

    public function test_customer_chat_keeps_order_selection_without_search_bar(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $order = \App\Models\Order::factory()->create([
            'user_id' => $user->id,
            'customer_name' => 'Khách cần tìm',
            'customer_phone' => '0911222333',
            'total_amount' => 2500000,
        ]);

        $this->actingAs($user)
            ->get(route('chat.index'))
            ->assertOk()
            ->assertDontSee('Tìm đơn theo tên, SĐT, mã đơn...')
            ->assertSee('aria-label="Chọn đơn hàng để đính kèm"', false)
            ->assertSee('#' . $order->id);
    }

    public function test_order_in_transit_cannot_be_cancelled(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $order = \App\Models\Order::factory()->create([
            'status' => 'awaiting_delivery',
            'total_amount' => 2500000,
        ]);

        $response = $this->actingAs($admin)
            ->from('/admin/orders')
            ->post('/admin/orders/' . $order->id . '/status', [
                'status' => 'cancelled',
            ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'awaiting_delivery',
        ]);
    }

    public function test_completed_cod_order_stays_pending_until_finance_confirmation(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $order = \App\Models\Order::factory()->create([
            'payment_method' => 'cod',
            'status' => 'awaiting_delivery',
            'total_amount' => 2500000,
        ]);

        \App\Models\PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'cod',
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post('/admin/orders/' . $order->id . '/status', [
                'status' => 'completed',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'gateway' => 'cod',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);
    }

    public function test_finance_can_mark_completed_cod_order_as_paid(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $order = \App\Models\Order::factory()->create([
            'payment_method' => 'cod',
            'status' => 'completed',
            'total_amount' => 2500000,
        ]);

        $transaction = \App\Models\PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'cod',
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.finance.update-status', $order), [
                'payment_status' => 'paid',
                'current_payment_status' => 'pending',
                'current_order_status' => 'completed',
                'current_payment_id' => $transaction->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('payment_transactions', [
            'id' => $transaction->id,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);
    }

    public function test_finance_transactions_uses_bootstrap_pagination(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        foreach (range(1, 16) as $index) {
            $order = \App\Models\Order::factory()->create([
                'payment_method' => 'cod',
                'created_at' => now()->subMinutes($index),
            ]);
            $order->paymentTransactions()->create([
                'gateway' => 'cod',
                'amount' => $order->total_amount,
                'status' => 'pending',
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.finance.transactions'))
            ->assertOk()
            ->assertSee('class="pagination"', false)
            ->assertDontSee('Showing 1 to 15 of 16 results');
    }

    public function test_finance_admin_cannot_set_cod_payment_to_failed(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $order = \App\Models\Order::factory()->create([
            'payment_method' => 'cod',
            'status' => 'pending_confirmation',
        ]);
        $payment = $order->paymentTransactions()->create([
            'gateway' => 'cod',
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.finance.transactions'))
            ->patch(route('admin.finance.update-status', $order), [
                'payment_status' => 'failed',
                'current_payment_status' => 'pending',
                'current_order_status' => $order->status,
                'current_payment_id' => $payment->id,
            ])
            ->assertSessionHasErrors('payment_status');

        $this->assertDatabaseHas('payment_transactions', [
            'id' => $payment->id,
            'status' => 'pending',
        ]);
    }

    public function test_pending_payment_is_not_displayed_as_paid(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $order = \App\Models\Order::factory()->create([
            'user_id' => $user->id,
            'payment_method' => 'visa',
            'status' => 'paid',
        ]);

        \App\Models\PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'momo',
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get('/orders')
            ->assertOk()
            ->assertSee('Chưa thanh toán')
            ->assertDontSee('Đã thanh toán');
    }

    public function test_completed_cod_order_with_pending_transaction_is_unpaid(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $order = \App\Models\Order::factory()->create([
            'user_id' => $user->id,
            'payment_method' => 'cod',
            'status' => 'completed',
        ]);

        \App\Models\PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'cod',
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get('/orders')
            ->assertOk()
            ->assertSee('Chưa thanh toán')
            ->assertDontSee('Đã thanh toán');
    }

    public function test_staff_can_access_only_product_and_order_management(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($staff)->get(route('air_conditioners.index'))->assertOk();
        $this->actingAs($staff)->get(route('admin.orders.index'))->assertOk();
        $this->actingAs($staff)->get(route('admin.chat.index'))->assertOk();
        $this->actingAs($staff)->get(route('admin.finance.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.reports.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.staff.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('chat.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('user.orders.index'))->assertForbidden();
    }

    public function test_admin_can_create_update_and_delete_staff_accounts(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('admin.staff.index'))->assertOk();

        $this->post(route('admin.staff.store'), [
            'name' => 'Nhân viên mới',
            'email' => 'staff@example.test',
            'password' => 'initial-password',
            'password_confirmation' => 'initial-password',
        ])->assertRedirect(route('admin.staff.index'));

        $staff = User::where('email', 'staff@example.test')->firstOrFail();
        $this->assertSame('staff', $staff->role);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('initial-password', $staff->password));

        $this->put(route('admin.staff.update', $staff), [
            'name' => 'Nhân viên đã sửa',
            'email' => 'staff.updated@example.test',
            'password' => 'updated-password',
            'password_confirmation' => 'updated-password',
        ])->assertRedirect(route('admin.staff.index'));

        $staff->refresh();
        $this->assertSame('staff', $staff->role);
        $this->assertSame('Nhân viên đã sửa', $staff->name);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('updated-password', $staff->password));

        $this->delete(route('admin.staff.destroy', $staff))
            ->assertRedirect(route('admin.staff.index'));
        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    public function test_staff_can_reply_to_customers_and_only_admin_sees_staff_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Quản trị HC']);
        $staff = User::factory()->create(['role' => 'staff', 'name' => 'Nhân viên Lan']);
        $customer = User::factory()->create(['name' => 'Khách Minh']);

        $this->actingAs($staff)
            ->post(route('admin.chat.send'), [
                'customer_id' => $customer->id,
                'message' => 'Nhân viên đang tư vấn cho khách.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('chat_messages', [
            'sender_id' => $staff->id,
            'recipient_id' => $customer->id,
            'sender_role' => 'staff',
            'message' => 'Nhân viên đang tư vấn cho khách.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.chat.index', ['customer_id' => $customer->id]))
            ->assertOk()
            ->assertSee('Nhân viên Lan');

        $this->actingAs($customer)
            ->get(route('chat.index'))
            ->assertOk()
            ->assertSee('Shop')
            ->assertDontSee('Nhân viên Lan');
    }

    public function test_admin_can_search_and_update_customer_account_without_changing_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create([
            'role' => 'user',
            'name' => 'Nguyễn Minh Khách',
            'email' => 'customer@example.test',
        ]);
        \App\Models\Order::factory()->create([
            'user_id' => $customer->id,
            'customer_name' => 'Nguyễn Minh Khách',
            'customer_phone' => '0900111222',
            'total_amount' => 2500000,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.customers.index', ['keyword' => '0900111222']))
            ->assertOk()
            ->assertSee('customer@example.test');

        $this->actingAs($admin)
            ->put(route('admin.customers.update', $customer), [
                'name' => 'Nguyễn Minh đã cập nhật',
                'email' => 'customer.updated@example.test',
                'password' => '',
                'password_confirmation' => '',
                'keyword' => '0900111222',
            ])
            ->assertRedirect(route('admin.customers.index', ['keyword' => '0900111222']));

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Nguyễn Minh đã cập nhật',
            'email' => 'customer.updated@example.test',
            'role' => 'user',
        ]);
        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'customer_phone' => '0900111222',
        ]);
    }

    public function test_coupon_discount_is_recalculated_and_applied_to_order_and_payment(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $coupon = \App\Models\Coupon::create([
            'code' => 'SAVE1500',
            'discount_type' => 'fixed',
            'discount_value' => 1500000,
            'minimum_order' => 10000000,
            'usage_limit' => 1,
            'is_active' => true,
        ]);
        session()->put('cart', [
            '1_default' => [
                'product_id' => 1,
                'name' => 'Điều hòa Casper',
                'price' => 12000000,
                'quantity' => 2,
            ],
        ]);

        $this->actingAs($user)
            ->post(route('user.orders.store'), [
                'customer_name' => 'Nguyễn Văn A',
                'customer_phone' => '0909123456',
                'customer_address' => '123 Lê Lợi, Hà Nội',
                'payment_method' => 'cod',
                'selected_items' => ['1_default'],
                'coupon_code' => 'save1500',
            ])
            ->assertRedirect();

        $order = \App\Models\Order::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('SAVE1500', $order->coupon_code);
        $this->assertSame(1500000.0, (float) $order->discount_amount);
        $this->assertSame(22500000.0, (float) $order->total_amount);
        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'amount' => 22500000,
        ]);

        $this->actingAs($user)
            ->get(route('user.orders.show', $order))
            ->assertOk()
            ->assertSee('SAVE1500')
            ->assertSee('-1.500.000 đ');
    }

    public function test_coupon_preview_uses_server_cart_values_and_rejects_expired_codes(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        \App\Models\Coupon::create([
            'code' => 'PERCENT10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'minimum_order' => 0,
            'maximum_discount' => 500000,
            'is_active' => true,
        ]);
        \App\Models\Coupon::create([
            'code' => 'EXPIRED',
            'discount_type' => 'fixed',
            'discount_value' => 100000,
            'minimum_order' => 0,
            'expires_at' => now()->subDay(),
            'is_active' => true,
        ]);
        session()->put('cart', [
            '1_default' => ['name' => 'Điều hòa Casper', 'price' => 12000000, 'quantity' => 2],
        ]);

        $this->actingAs($user)
            ->postJson(route('user.coupons.validate'), [
                'coupon_code' => 'percent10',
                'selected_items' => ['1_default'],
            ])
            ->assertOk()
            ->assertJsonPath('discount', 500000);

        session()->put('cart', [
            '1_default' => ['name' => 'Điều hòa Casper', 'price' => 12000000, 'quantity' => 2],
        ]);
        $response = $this->actingAs($user)
            ->from(route('user.cart.index'))
            ->post(route('user.orders.store'), [
                'customer_name' => 'Nguyễn Văn A',
                'customer_phone' => '0909123456',
                'customer_address' => '123 Lê Lợi, Hà Nội',
                'payment_method' => 'cod',
                'selected_items' => ['1_default'],
                'coupon_code' => 'EXPIRED',
            ]);
        $response->assertSessionHasErrors('coupon_code');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_admin_and_staff_can_manage_coupons(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('admin.coupons.index'))
            ->assertOk();

        $this->actingAs($staff)
            ->post(route('admin.coupons.store'), [
                'code' => 'STAFF10',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'minimum_order' => 1000000,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.coupons.index'));

        $coupon = \App\Models\Coupon::where('code', 'STAFF10')->firstOrFail();
        $this->assertSame('percentage', $coupon->discount_type);

        $this->actingAs($staff)
            ->put(route('admin.coupons.update', $coupon), [
                'code' => 'STAFF15',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'minimum_order' => 2000000,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.coupons.index'));

        $this->assertDatabaseHas('coupons', ['id' => $coupon->id, 'code' => 'STAFF15']);
    }

    public function test_online_payment_options_use_visa_and_domestic_card_through_momo_gateway(): void
    {
        Http::fake(fn () => Http::response([
            'resultCode' => 0,
            'payUrl' => 'https://pay.example.test/checkout',
        ], 200));

        $user = User::factory()->create(['email_verified_at' => now()]);
        $methods = [
            'visa' => 'payWithCC',
            'domestic' => 'payWithATM',
        ];

        foreach ($methods as $method => $requestType) {
            $cartKey = '1_' . $method;
            session()->put('cart', [
                $cartKey => [
                    'product_id' => 1,
                    'name' => 'Điều hòa Casper',
                    'price' => 12000000,
                    'quantity' => 1,
                ],
            ]);

            $this->actingAs($user)
                ->post(route('user.orders.store'), [
                    'customer_name' => 'Nguyễn Văn A',
                    'customer_phone' => '0909123456',
                    'customer_address' => '123 Lê Lợi, Hà Nội',
                    'payment_method' => $method,
                    'selected_items' => [$cartKey],
                ])
                ->assertRedirect();

            $order = \App\Models\Order::where('user_id', $user->id)
                ->where('payment_method', $method)
                ->latest('id')->firstOrFail();

            $paymentResponse = $this->actingAs($user)->get(route('payment.momo.start', $order));
            $paymentResponse->assertRedirect('https://pay.example.test/checkout');
        }

        foreach (array_values($methods) as $requestType) {
            Http::assertSent(fn ($request) => $request['requestType'] === $requestType);
        }
        Http::assertSentCount(count($methods));

        $this->actingAs($user)
            ->post(route('user.orders.store'), [
                'customer_name' => 'Nguyễn Văn A',
                'customer_phone' => '0909123456',
                'customer_address' => '123 Lê Lợi, Hà Nội',
                'payment_method' => 'momo',
                'selected_items' => ['1_visa'],
            ])
            ->assertSessionHasErrors('payment_method');
    }

    public function test_customer_can_cancel_cod_before_shipping_but_not_online_or_shipped_orders(): void
    {
        $customer = User::factory()->create(['email_verified_at' => now()]);
        $codOrder = \App\Models\Order::factory()->create([
            'user_id' => $customer->id,
            'payment_method' => 'cod',
            'status' => 'pending_confirmation',
            'shipping_status' => 'not_shipped',
        ]);
        $codPayment = $codOrder->paymentTransactions()->create([
            'gateway' => 'cod',
            'amount' => $codOrder->total_amount,
            'status' => 'pending',
        ]);

        $this->actingAs($customer)
            ->get(route('user.orders.index'))
            ->assertOk()
            ->assertSee('Hủy đơn');

        $this->actingAs($customer)
            ->post(route('user.orders.cancel', $codOrder))
            ->assertRedirect(route('user.orders.index'));

        $this->actingAs($customer)
            ->get(route('user.orders.index'))
            ->assertOk()
            ->assertDontSee('Hủy đơn');

        $this->assertDatabaseHas('orders', [
            'id' => $codOrder->id,
            'status' => 'cancelled',
            'shipping_status' => 'cancelled',
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'id' => $codPayment->id,
            'status' => 'cancelled',
        ]);

        $onlineOrder = \App\Models\Order::factory()->create([
            'user_id' => $customer->id,
            'payment_method' => 'visa',
            'status' => 'pending_confirmation',
        ]);
        $this->actingAs($customer)
            ->from(route('user.orders.index'))
            ->post(route('user.orders.cancel', $onlineOrder))
            ->assertSessionHasErrors('order');

        $shippedOrder = \App\Models\Order::factory()->create([
            'user_id' => $customer->id,
            'payment_method' => 'cod',
            'status' => 'in_transit',
            'shipping_status' => 'delivering',
            'ghn_order_code' => 'GHN-123456',
        ]);
        $this->actingAs($customer)
            ->from(route('user.orders.index'))
            ->post(route('user.orders.cancel', $shippedOrder))
            ->assertSessionHasErrors('order');
    }

    public function test_cancelled_order_cannot_be_restored_by_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $order = \App\Models\Order::factory()->create([
            'status' => 'cancelled',
            'shipping_status' => 'cancelled',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->post(route('admin.orders.updateStatus', $order->id), ['status' => 'awaiting_delivery'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Đơn đã hủy, không thể cập nhật sang trạng thái khác.')
            ->assertDontSee('name="status"', false);
    }
}
