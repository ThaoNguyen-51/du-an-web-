<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::created(function (Order $order): void {
            if (!$order->virtual_tracking_code) {
                do {
                    $code = Str::upper(Str::random(6));
                } while (static::where('virtual_tracking_code', $code)->exists());

                $order->forceFill(['virtual_tracking_code' => $code])->saveQuietly();
            }
        });
    }

    public const STATUS_PENDING_CONFIRMATION = 'pending_confirmation';
    public const STATUS_AWAITING_PICKUP = 'awaiting_pickup';
    public const STATUS_AWAITING_DELIVERY = 'awaiting_delivery';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const ONLINE_PAYMENT_METHODS = ['visa', 'domestic'];

    public static function isOnlinePaymentMethod(?string $method): bool
    {
        return in_array($method, self::ONLINE_PAYMENT_METHODS, true);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING_CONFIRMATION => 'Chờ xác nhận',
            self::STATUS_AWAITING_PICKUP => 'Chờ lấy hàng',
            self::STATUS_AWAITING_DELIVERY => 'Chờ giao hàng',
            self::STATUS_IN_TRANSIT => 'Đang giao',
            self::STATUS_COMPLETED => 'Đã hoàn thành',
            self::STATUS_CANCELLED => 'Đã hủy',
        ];
    }

    public static function normalizeStatus(?string $status): ?string
    {
        $map = [
            'pending' => self::STATUS_PENDING_CONFIRMATION,
            'pending_confirmation' => self::STATUS_PENDING_CONFIRMATION,
            'paid' => self::STATUS_PENDING_CONFIRMATION,
            'processing' => self::STATUS_AWAITING_PICKUP,
            'awaiting_pickup' => self::STATUS_AWAITING_PICKUP,
            'awaiting_delivery' => self::STATUS_AWAITING_DELIVERY,
            'in_transit' => self::STATUS_IN_TRANSIT,
            'delivered' => self::STATUS_IN_TRANSIT,
            'completed' => self::STATUS_COMPLETED,
            'cancelled' => self::STATUS_CANCELLED,
        ];

        return $map[$status] ?? $status;
    }

    public function getStatusLabelAttribute(): string
    {
        $status = self::normalizeStatus($this->status);

        return self::statusOptions()[$status] ?? ucfirst(str_replace('_', ' ', $status ?? 'unknown'));
    }

    public function getStatusMessageAttribute(): string
    {
        return match (self::normalizeStatus($this->status) ?? '') {
            self::STATUS_PENDING_CONFIRMATION => 'Đơn hàng đang chờ xác nhận từ shop.',
            self::STATUS_AWAITING_PICKUP => 'Đơn hàng đã được xác nhận và đang chờ người bán lấy hàng.',
            self::STATUS_AWAITING_DELIVERY => 'Đơn hàng đang chờ giao hàng tới bạn.',
            self::STATUS_IN_TRANSIT => 'Đơn hàng đang trên đường giao tới bạn.',
            self::STATUS_COMPLETED => 'Đơn hàng đã hoàn tất và được xác nhận hoàn thành.',
            self::STATUS_CANCELLED => 'Đơn hàng đã bị hủy.',
            default => 'Đơn hàng đang được xử lý.',
        };
    }

    protected $fillable = [
        'user_id',
        'order_code',
        'customer_name',
        'customer_phone',
        'customer_address',
        'note',
        'payment_method',
        'subtotal',
        'shipping_fee',
        'coupon_id',
        'coupon_code',
        'discount_amount',
        'total_amount',
        'ghn_total_fee',
        'to_district_id',
        'to_ward_code',
        'shipping_status',
        'ghn_order_code',
        'virtual_tracking_code',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function messages()
    {
        return $this->hasMany(OrderMessage::class)->orderBy('created_at', 'asc');
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'asc');
    }
}