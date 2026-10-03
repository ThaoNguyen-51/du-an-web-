<?php

namespace Tests\Feature;

use App\Models\AirConditioner;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_review_a_product_from_their_completed_order(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => Order::STATUS_COMPLETED,
        ]);
        $this->addProductToOrder($order, $product);

        $this->actingAs($customer)
            ->from(route('shop.detail', $product->id))
            ->post(route('product-reviews.store', $product), [
                'order_id' => $order->id,
                'rating' => 5,
                'comment' => 'Sản phẩm hoạt động tốt, làm lạnh nhanh.',
            ])
            ->assertRedirect(route('shop.detail', $product->id))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('product_reviews', [
            'air_conditioner_id' => $product->id,
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'rating' => 5,
        ]);

        $this->actingAs($customer)
            ->from(route('shop.detail', $product->id))
            ->post(route('product-reviews.store', $product), [
                'order_id' => $order->id,
                'rating' => 4,
                'comment' => 'Gửi đánh giá lần thứ hai.',
            ])
            ->assertSessionHasErrors('order_id');

        $this->assertDatabaseCount('product_reviews', 1);
    }

    public function test_customer_cannot_review_without_a_completed_purchase(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();
        $otherProduct = $this->createProduct('Sản phẩm khác');
        $pendingOrder = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => Order::STATUS_IN_TRANSIT,
        ]);
        $this->addProductToOrder($pendingOrder, $product);

        $this->actingAs($customer)
            ->from(route('shop.detail', $product->id))
            ->post(route('product-reviews.store', $product), [
                'order_id' => $pendingOrder->id,
                'rating' => 4,
                'comment' => 'Chưa hoàn thành đơn hàng.',
            ])
            ->assertSessionHasErrors('order_id');

        $this->actingAs($customer)
            ->get(route('shop.detail', $product->id))
            ->assertOk()
            ->assertSee('có thể gửi đánh giá sau khi đơn chuyển sang trạng thái Đã hoàn thành')
            ->assertSee('Đang giao');

        $completedOtherOrder = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => Order::STATUS_COMPLETED,
        ]);
        $this->addProductToOrder($completedOtherOrder, $otherProduct);

        $this->actingAs($customer)
            ->from(route('shop.detail', $product->id))
            ->post(route('product-reviews.store', $product), [
                'order_id' => $completedOtherOrder->id,
                'rating' => 4,
                'comment' => 'Không mua sản phẩm này.',
            ])
            ->assertSessionHasErrors('order_id');

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_non_buyer_can_read_reviews_but_cannot_see_review_form(): void
    {
        $buyer = User::factory()->create(['role' => 'customer']);
        $reader = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();
        $order = Order::factory()->create([
            'user_id' => $buyer->id,
            'status' => Order::STATUS_COMPLETED,
        ]);
        $this->addProductToOrder($order, $product);
        ProductReview::create([
            'air_conditioner_id' => $product->id,
            'user_id' => $buyer->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Đánh giá công khai.',
        ]);

        $this->actingAs($reader)
            ->get(route('shop.detail', $product->id))
            ->assertOk()
            ->assertSee('Đánh giá công khai.')
            ->assertDontSee('Viết đánh giá');
    }

    public function test_completed_order_history_and_detail_expose_review_action(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => Order::STATUS_COMPLETED,
        ]);
        $this->addProductToOrder($order, $product);

        $this->actingAs($customer)
            ->get(route('user.orders.index'))
            ->assertOk()
            ->assertSee('Đánh giá');

        $this->get(route('user.orders.show', $order))
            ->assertOk()
            ->assertSee('Gửi đánh giá')
            ->assertSee(route('product-reviews.store', $product));
    }

    public function test_legacy_completed_order_can_qualify_by_stored_product_name(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => Order::STATUS_COMPLETED,
        ]);
        $order->items()->create([
            'air_conditioner_id' => null,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
            'subtotal' => $product->price,
        ]);

        $this->actingAs($customer)
            ->get(route('shop.detail', $product->id))
            ->assertOk()
            ->assertSee('Viết đánh giá');
    }

    public function test_admin_can_reply_to_a_product_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => Order::STATUS_COMPLETED,
        ]);
        $review = ProductReview::create([
            'air_conditioner_id' => $product->id,
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'rating' => 4,
            'comment' => 'Sản phẩm tốt.',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.product-reviews.reply', $review), [
                'admin_reply' => 'Cảm ơn bạn đã tin tưởng cửa hàng.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('product_reviews', [
            'id' => $review->id,
            'admin_reply' => 'Cảm ơn bạn đã tin tưởng cửa hàng.',
            'replied_by' => $admin->id,
        ]);
    }

    private function createProduct(string $name = 'Điều hòa kiểm thử'): AirConditioner
    {
        return AirConditioner::create([
            'name' => $name,
            'brand' => 'Casper',
            'price' => 10000000,
        ]);
    }

    private function addProductToOrder(Order $order, AirConditioner $product): void
    {
        $order->items()->create([
            'air_conditioner_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
            'subtotal' => $product->price,
        ]);
    }
}