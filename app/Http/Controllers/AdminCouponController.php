<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminCouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::withCount('orders')->latest()->paginate(20);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['used_count'] = 0;
        $data['is_active'] = $request->boolean('is_active');
        Coupon::create($data);

        return redirect()->route('admin.coupons.index')->with('success', 'Đã tạo mã giảm giá.');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $this->validatedData($request, $coupon);
        if ($data['usage_limit'] !== null && $data['usage_limit'] < $coupon->used_count) {
            throw ValidationException::withMessages(['usage_limit' => 'Giới hạn lượt dùng không thể nhỏ hơn số lượt đã sử dụng.']);
        }

        $data['is_active'] = $request->boolean('is_active');
        $coupon->update($data);

        return redirect()->route('admin.coupons.index')->with('success', 'Đã cập nhật mã giảm giá.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', 'Đã xóa mã giảm giá.');
    }

    private function validatedData(Request $request, ?Coupon $coupon = null): array
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);

        $data = $request->validate([
            'code' => ['required', 'string', 'alpha_dash', 'max:40', Rule::unique('coupons', 'code')->ignore($coupon?->id)],
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'required|numeric|gt:0',
            'minimum_order' => 'required|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|gt:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'usage_limit' => 'nullable|integer|min:1',
        ]);

        if ($data['discount_type'] === 'percentage' && $data['discount_value'] > 100) {
            throw ValidationException::withMessages(['discount_value' => 'Mức giảm theo phần trăm không được vượt quá 100%.']);
        }

        if (!empty($data['starts_at']) && !empty($data['expires_at']) && $data['expires_at'] < $data['starts_at']) {
            throw ValidationException::withMessages(['expires_at' => 'Thời điểm kết thúc phải sau thời điểm bắt đầu.']);
        }

        $data['maximum_discount'] = $data['maximum_discount'] ?? null;
        $data['starts_at'] = $data['starts_at'] ?? null;
        $data['expires_at'] = $data['expires_at'] ?? null;
        $data['usage_limit'] = $data['usage_limit'] ?? null;

        return $data;
    }
}
