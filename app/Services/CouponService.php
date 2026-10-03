<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function findApplicable(string $code, float $subtotal, bool $lock = false): Coupon
    {
        $query = Coupon::query()->where('code', strtoupper(trim($code)));
        if ($lock) {
            $query->lockForUpdate();
        }

        $coupon = $query->first();
        if (!$coupon || !$coupon->is_active) {
            throw ValidationException::withMessages(['coupon_code' => 'Mã giảm giá không tồn tại hoặc đã ngừng hoạt động.']);
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            throw ValidationException::withMessages(['coupon_code' => 'Mã giảm giá chưa đến thời gian sử dụng.']);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            throw ValidationException::withMessages(['coupon_code' => 'Mã giảm giá đã hết hạn.']);
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw ValidationException::withMessages(['coupon_code' => 'Mã giảm giá đã hết lượt sử dụng.']);
        }

        if ($subtotal < $coupon->minimum_order) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format($coupon->minimum_order, 0, ',', '.') . ' đ để dùng mã này.',
            ]);
        }

        return $coupon;
    }

    public function calculateDiscount(Coupon $coupon, float $subtotal): int
    {
        $discount = $coupon->discount_type === 'percentage'
            ? round($subtotal * $coupon->discount_value / 100)
            : round($coupon->discount_value);

        if ($coupon->maximum_discount !== null) {
            $discount = min($discount, (int) round($coupon->maximum_discount));
        }

        return min(max(0, $discount), (int) floor($subtotal));
    }
}
