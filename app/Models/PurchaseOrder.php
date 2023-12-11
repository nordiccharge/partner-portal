<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'team_id',
        'status',
        'use_dropshipping',
        'shipping_address',
        'postal',
        'city',
        'country',
        'tracking_code',
        'note'
    ];

    public function team(): BelongsTo {
        return $this->belongsTo(Team::class);
    }

    public function items(): HasMany {
        return $this->hasMany(PurchaseOrderItem::class);
    }

}
