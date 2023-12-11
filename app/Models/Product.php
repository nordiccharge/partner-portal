<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'brand_id',
        'category_id',
        'description',
        'retail_price',
        'purchase_price',
        'image_url',
        'delivery_information',
        'quantity',
        'is_active'
    ];

    public function brand(): BelongsTo {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function inventories(): HasMany {
        return $this->hasMany(Inventory::class);
    }

    public function getName(): string {
        return $this->name;
    }

    public function orders(): HasOneThrough {
        return $this->hasManyThrough(Inventory::class, OrderItem::class);
    }

}
