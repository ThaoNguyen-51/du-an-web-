<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use Illuminate\Database\Eloquent\Model;

class AdminActivityLogger
{
    public function record(string $action, string $summary, ?Model $subject = null, array $properties = []): AdminActivityLog
    {
        return AdminActivityLog::create([
            'actor_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'summary' => $summary,
            'properties' => $properties ?: null,
            'ip_address' => request()->ip(),
        ]);
    }
}
