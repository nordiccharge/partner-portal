<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use HasFactory, LogsActivity, HasFilamentComments;

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
        'note',
        'installation_required',
        'installation_id',
        'installation_price',
        'nc_price',
        'installer_id',
        'pending_action',
        'action'
    ];

    protected static $logAttributes = ['*'];

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

    public function installation(): BelongsTo {
        return $this->belongsTo(Installation::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logExcept(['updated_at'])
            ->logOnlyDirty();
    }

    public function invoices(): MorphMany
    {
        return $this->morphMany(Invoice::class, 'invoiceable');
    }

    public function setInstallationRequiredAttribute($value): void
    {
        if (!$value) {
            $this->attributes['installation_required'] = 0;
            $this->attributes['installation_id'] = null;
            $this->attributes['installer_id'] = null;
            $this->attributes['installation_price'] = null;
            $this->attributes['installation_date'] = null;
            $this->attributes['wished_installation_date'] = null;
        } else {
            $this->attributes['installation_required'] = 1;
        }
    }

    protected function fullName(): Attribute {
        return Attribute::make(
            fn ($value, $attributes) => $attributes['customer_first_name'] . ' ' . $attributes['customer_last_name'],
        );
    }

    protected function installerCompany(): HasOneThrough {
        return $this->hasOneThrough(Company::class, Installer::class, 'id', 'id', 'installer_id', 'company_id');
    }

}
