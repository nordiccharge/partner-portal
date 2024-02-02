<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Postal extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'city_id',
        'postal',
        'installer_id',
        'active'
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

    public function installerPostals(): HasMany {
        return $this->hasMany(InstallerPostal::class);
    }

    public function orders(): HasMany {
        return $this->hasMany(Order::class);
    }
}
