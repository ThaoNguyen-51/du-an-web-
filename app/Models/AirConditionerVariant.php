<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirConditionerVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'air_conditioner_id',
        'capacity_name',
        'price',
        'stock',
        'weight',
        'image',
        'specifications',
    ];

    // Ép kiểu thuộc tính specifications từ string JSON sang Array
    protected $casts = [
        'specifications' => 'array',
        'price' => 'decimal:2',
        'weight' => 'integer',
        'stock' => 'integer',
    ];

    public function airConditioner()
    {
        return $this->belongsTo(AirConditioner::class);
    }
}