<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_name',
        'capacity_name',
        'price',
        'quantity',
        'subtotal',
        'air_conditioner_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function airConditioner()
    {
        return $this->belongsTo(AirConditioner::class);
    }
}