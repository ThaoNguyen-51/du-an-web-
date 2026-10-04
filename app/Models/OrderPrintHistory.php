<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPrintHistory extends Model
{
    protected $fillable = [
        'order_id',
        'printed_by',
        'print_type',
        'printed_at',
        'ip_address',
    ];

    protected $casts = [
        'printed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function printer()
    {
        return $this->belongsTo(User::class, 'printed_by');
    }
}
