<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirConditionerImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'air_conditioner_id',
        'image_path',
        'sort_order',
    ];

    public function airConditioner()
    {
        return $this->belongsTo(AirConditioner::class);
    }
}
