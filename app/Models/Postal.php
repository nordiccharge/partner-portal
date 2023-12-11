<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Postal extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'city_id',
        'postal',
        'installer_id'
    ];

    public function country(): BelongsTo {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo {
        return $this->belongsTo(City::class);
    }

    public function installer(): BelongsTo {
        return $this->belongsTo(Installer::class);
    }
}
