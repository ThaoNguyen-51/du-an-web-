<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WishlistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'air_conditioner_id', 'price_snapshot', 'stock_snapshot',
    ];

    protected $casts = [
        'price_snapshot' => 'decimal:2',
        'stock_snapshot' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(AirConditioner::class, 'air_conditioner_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
