<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'user_id',
        'rating',
        'comment',
    ];

    /**
     * Relacja n:1 – recenzja dotyczy jednej usługi.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Relacja n:1 – recenzja napisana przez jednego użytkownika.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
