<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'inventory_id',
        'quantity',
        'price'
    ];

    public function order(): BelongsTo {
        return $this->belongsTo(Order::class);
    }

    public function inventory(): BelongsTo {
        return $this->belongsTo(Inventory::class);
    }

}
