<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_type_id',
        'name',
        'description',
        'contact_email',
        'contact_phone',
        'invoice_email',
        'sender_name',
        'sender_attention',
        'sender_address',
        'sender_address2',
        'sender_zip',
        'sender_city',
        'sender_country',
        'sender_state',
        'sender_phone',
        'sender_email'
    ];

    public function teams(): HasMany {
        return $this->hasMany(Team::class);
    }

    public function companyType(): BelongsTo {
        return $this->belongsTo(CompanyType::class);
    }

}
