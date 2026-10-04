<?php

namespace App\Http\Controllers;

use App\Models\AirConditioner;
use App\Models\WishlistItem;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $items = $request->user()->wishlistItems()->with(['product.images', 'product.variants'])->latest()->get();
        $items->each(function (WishlistItem $item) {
            $item->current_price = (float) ($item->product->variants->first()?->price ?? $item->product->price ?? 0);
            $item->current_stock = (int) $item->product->variants->sum('stock');
            $item->price_dropped = $item->price_snapshot !== null && $item->current_price < (float) $item->price_snapshot;
            $item->back_in_stock = (int) $item->stock_snapshot <= 0 && $item->current_stock > 0;
        });

        return view('shop.wishlist', compact('items'));
    }

    public function toggle(Request $request, AirConditioner $product)
    {
        $item = $request->user()->wishlistItems()->where('air_conditioner_id', $product->id)->first();
        if ($item) {
            $item->delete();
            if ($request->expectsJson()) {
                return response()->json([
                    'wishlisted' => false,
                    'message' => 'Đã xóa sản phẩm khỏi danh sách yêu thích.',
                ]);
            }
            return back()->with('success', 'Đã xóa sản phẩm khỏi danh sách yêu thích.');
        }

        $request->user()->wishlistItems()->create([
            'air_conditioner_id' => $product->id,
            'price_snapshot' => $product->variants()->value('price') ?? $product->price,
            'stock_snapshot' => (int) $product->variants()->sum('stock'),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'wishlisted' => true,
                'message' => 'Đã lưu sản phẩm vào danh sách yêu thích.',
            ]);
        }

        return back()->with('success', 'Đã lưu sản phẩm vào danh sách yêu thích.');
    }
}
