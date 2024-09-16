<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'description',
        'valid_until',
    ];

    /**
     * Relacja n:1 – promocja dotyczy jednej usługi.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
