<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminActivityLog extends Model
{
    protected $fillable = [
        'actor_id', 'action', 'subject_type', 'subject_id', 'summary',
        'properties', 'ip_address',
    ];

    protected $casts = ['properties' => 'array'];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function subject()
    {
        return $this->morphTo();
    }
}
