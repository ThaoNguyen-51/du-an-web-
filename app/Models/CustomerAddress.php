<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'label', 'recipient_name', 'phone', 'address',
        'province', 'province_id', 'district', 'district_id', 'ward', 'ward_code', 'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'province_id' => 'integer',
        'district_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
