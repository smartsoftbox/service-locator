<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    // pola które można masowo wypełniać
    protected $fillable = ['name', 'description'];

    public function services() : HasMany 
    {
        return $this->hasMany(Service::class);
    }
}
