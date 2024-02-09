<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Inventory extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'team_id',
        'product_id',
        'quantity',
        'sale_price',
    ];

    protected static $logAttributes = ['*'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function team(): BelongsTo {
        return $this->belongsTo(Team::class);
    }
    public function product(): BelongsTo {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function orderItems(): HasMany {
        return $this->hasMany(OrderItem::class);
    }

}
