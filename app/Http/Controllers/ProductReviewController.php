<?php

namespace App\Http\Controllers;

use App\Models\AirConditioner;
use App\Models\Order;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductReviewController extends Controller
{
    public function store(Request $request, AirConditioner $product)
    {
        $data = $request->validate([
            'order_id' => 'required|integer',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'required|string|min:5|max:2000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|url|max:2048',
        ]);

        $order = $request->user()->orders()
            ->whereKey($data['order_id'])
            ->where('status', Order::STATUS_COMPLETED)
            ->whereHas('items', function ($query) use ($product) {
                $query->where('air_conditioner_id', $product->id)
                    ->orWhere(function ($legacyQuery) use ($product) {
                        $legacyQuery->whereNull('air_conditioner_id')
                            ->where('product_name', $product->name);
                    });
            })
            ->first();

        if (!$order) {
            throw ValidationException::withMessages([
                'order_id' => 'Chỉ có thể đánh giá sản phẩm trong đơn hàng đã hoàn thành của bạn.',
            ]);
        }

        if (ProductReview::where('order_id', $order->id)
            ->where('air_conditioner_id', $product->id)
            ->exists()) {
            throw ValidationException::withMessages([
                'order_id' => 'Bạn đã đánh giá sản phẩm này trong đơn hàng đó rồi.',
            ]);
        }

        $imageUrls = collect($data['images'] ?? [])
            ->map(fn ($url) => trim($url))
            ->filter()
            ->values()
            ->all();

        $product->reviews()->create([
            'user_id' => $request->user()->id,
            'order_id' => $order->id,
            'rating' => $data['rating'],
            'comment' => trim($data['comment']),
            'images' => $imageUrls ?: null,
        ]);

        return back()->with('success', 'Cảm ơn bạn đã gửi đánh giá sản phẩm.');
    }

    public function reply(Request $request, ProductReview $review)
    {
        $data = $request->validate([
            'admin_reply' => 'required|string|min:2|max:2000',
        ]);

        $review->update([
            'admin_reply' => trim($data['admin_reply']),
            'replied_by' => $request->user()->id,
            'replied_at' => now(),
        ]);

        return back()->with('success', 'Đã gửi phản hồi đánh giá.');
    }
}