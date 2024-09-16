<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'address',
        'latitude',
        'longitude',
        'rating',
        'review_count',
        'opening_hours',
    ];

    protected $casts = [
        'opening_hours' => 'array',  // Automatically cast to array
    ];
    
    /**
     * Relacja n:1 – usługa należy do jednej kategorii.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relacja 1:n – usługa ma wiele recenzji.
     */
    public function reviews():HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Relacja 1:n – usługa ma wiele promocji.
     */
    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    /**
     * Relacja 1:1 – usługa może mieć jedną subskrypcję.
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }
}
