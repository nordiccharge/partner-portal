<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name'
    ];

    public function cities(): HasMany {
        return $this->hasMany(City::class);
    }

    public function postals(): HasManyThrough {
        return $this->hasManyThrough(City::class, Postal::class);
    }

}
