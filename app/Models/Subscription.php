<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'plan',
        'started_at',
        'expires_at',
    ];

    /**
     * Relacja 1:1 – subskrypcja odnosi się do jednej usługi.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
