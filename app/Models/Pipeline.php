<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pipeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'shipping_type'
    ];

    public function stages(): HasMany {
        return $this->hasMany(Stage::class);
    }

    public function orders(): HasMany {
        return $this->hasMany(Order::class);
    }
}
