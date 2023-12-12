<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'team_id',
        'pipeline_id',
        'stage_id',
        'id',
        'order_reference',
        'customer_first_name',
        'customer_last_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'postal_id',
        'city_id',
        'country_id',
        'tracking_code',
        'wished_installation_date',
        'installation_date',
        'note'
    ];

    public function team(): BelongsTo {
        return $this->belongsTo(Team::class);
    }

    public function pipeline(): BelongsTo {
        return $this->belongsTo(Pipeline::class);
    }

    public function stage(): BelongsTo {
        return $this->belongsTo(Stage::class);
    }

    public function items(): HasMany {
        return $this->hasMany(OrderItem::class);
    }

    public function chargers(): HasMany {
        return $this->hasMany(Charger::class);
    }

    public function installer(): BelongsTo {
        return $this->belongsTo(Installer::class);
    }

    public function city(): BelongsTo {
        return $this->belongsTo(City::class);
    }

    public function country(): BelongsTo {
        return $this->belongsTo(Country::class);
    }

    public function postal(): BelongsTo {
        return $this->belongsTo(Postal::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
