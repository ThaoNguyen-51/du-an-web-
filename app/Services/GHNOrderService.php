<?php

namespace App\Services;

use App\Models\Order;

class GHNOrderService
{
    public function __construct(private GHNService $ghn) {}

    public function create(Order $order, bool $isPaid = false): array
    {
        $items = [];
        $weight = 0;

        foreach ($order->items as $item) {
            // Mặc định bộ điều hòa (Dàn nóng + Dàn lạnh) khoảng 25,000g (25kg)
            $itemWeight = 25000;
            $weight += $itemWeight * (int) $item->quantity;
            $items[] = [
                'name' => $item->product_name ?? 'Điều hòa',
                'quantity' => (int) $item->quantity,
                'price' => (int) $item->price,
                'weight' => $itemWeight,
            ];
        }

        return $this->ghn->createOrder([
            'payment_type_id' => 2,
            'note' => 'Giao hàng Điều hòa - Đơn #' . $order->id,
            'required_note' => 'KHONGCHOXEMHANG',
            'to_name' => $order->customer_name,
            'to_phone' => $order->customer_phone,
            'to_address' => $order->customer_address,
            'to_ward_code' => (string) $order->to_ward_code,
            'to_district_id' => (int) $order->to_district_id,
            'cod_amount' => $isPaid ? 0 : (int) $order->total_amount,
            'weight' => $weight > 0 ? $weight : 25000,
            'length' => 80, // Kích thước thùng đóng gói điều hòa (cm)
            'width' => 40,
            'height' => 60,
            'service_type_id' => 2,
            'items' => $items,
        ]);
    }
}