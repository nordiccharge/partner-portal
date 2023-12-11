<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Installer extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'contact_email',
        'contact_phone',
        'invoice_email',
    ];

    public function company(): BelongsTo {
        return $this->belongsTo(Company::class);
    }

    public function postals(): HasMany {
        return $this->hasMany(Postal::class);
    }

    public function orders(): HasMany {
        return $this->hasMany(Order::class);
    }
}
