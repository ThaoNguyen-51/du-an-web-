<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AirConditioner;
use App\Models\AirConditionerVariant;

class CartController extends Controller
{
    /**
     * Hiển thị trang giỏ hàng
     */
    public function index()
    {
        // Lấy danh sách sản phẩm trong session cart, mặc định là mảng rỗng
        $cart = session()->get('cart', []);
        $savedAddresses = auth()->user()->addresses()->orderByDesc('is_default')->latest()->get();
        
        return view('shop.cart', compact('cart', 'savedAddresses'));
    }

    /**
     * Thêm sản phẩm (hoặc biến thể công suất BTU) vào giỏ hàng
     */
    public function addToCart(Request $request, $id)
    {
        $product = AirConditioner::findOrFail($id);
        
        // Lấy phiên bản BTU người dùng chọn (nếu có)
        $variantId = $request->input('variant_id');
        $variant = $variantId ? AirConditionerVariant::find($variantId) : null;

        $cart = session()->get('cart', []);

        // Tạo khóa định danh riêng cho giỏ hàng (SP + Mã biến thể)
        $cartKey = $id . '_' . ($variantId ?? 'default');

        // Lấy tên phiên bản công suất
        $capacityName = $variant ? $variant->capacity_name : 'Mặc định';
        
        // Lấy giá bán (ưu tiên giá riêng của biến thể BTU)
        $price = $variant && $variant->price ? $variant->price : $product->price;

        // Lấy ảnh hiển thị
        $image = $product->primary_image_path;

        // Nếu sản phẩm đã có trong giỏ -> Cộng dồn số lượng
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $request->input('quantity', 1);
        } else {
            // Nếu chưa có -> Thêm mới vào giỏ
            $cart[$cartKey] = [
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'name'       => $product->name,
                'brand'      => $product->brand,
                'capacity'   => $capacityName,
                'price'      => (float) $price,
                'weight'     => (int) ($product->weight ?? 25000), // BỔ SUNG TRƯỜNG NÀY (lấy từ DB hoặc mặc định 25kg)
                'quantity'   => (int) $request->input('quantity', 1),
                'image'      => $image,
            ];
        }

        // Lưu lại vào Session
        session()->put('cart', $cart);

        if ($request->boolean('buy_now')) {
            session()->put('buy_now_key', $cartKey);

            return redirect()->route('user.cart.index');
        }

        session()->forget('buy_now_key');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đã thêm sản phẩm vào giỏ hàng thành công!',
                'cart_count' => collect($cart)->sum('quantity'),
            ]);
        }

        return redirect()->route('user.cart.index')->with('success', 'Đã thêm sản phẩm vào giỏ hàng thành công!');
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng
     */
    public function update(Request $request)
    {
        if ($request->key && $request->quantity) {
            $cart = session()->get('cart', []);

            if (isset($cart[$request->key])) {
                $cart[$request->key]['quantity'] = max(1, (int)$request->quantity);
                session()->put('cart', $cart);

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Đã cập nhật số lượng!',
                        'cart_count' => collect($cart)->sum('quantity'),
                    ]);
                }

                return redirect()->back()->with('success', 'Đã cập nhật số lượng!');
            }
        }

        return redirect()->back()->with('error', 'Không tìm thấy sản phẩm!');
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public function remove(Request $request)
    {
        if ($request->key) {
            $cart = session()->get('cart', []);

            if (isset($cart[$request->key])) {
                unset($cart[$request->key]);
                session()->put('cart', $cart);

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Đã xóa sản phẩm khỏi giỏ hàng!',
                        'cart_count' => collect($cart)->sum('quantity'),
                    ]);
                }

                return redirect()->back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');
            }
        }

        return redirect()->back()->with('error', 'Xóa thất bại!');
    }

    /**
     * Xóa sạch giỏ hàng
     */
    public function clear(Request $request)
    {
        session()->forget('cart');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đã xóa toàn bộ giỏ hàng!',
                'cart_count' => 0,
            ]);
        }

        return redirect()->back()->with('success', 'Đã xóa toàn bộ giỏ hàng!');
    }
}