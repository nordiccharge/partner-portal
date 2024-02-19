<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'description',
        'secret_key',
        'endpoint',
        'endpoint_url',
        'basic_api',
        'shipping_api_send',
        'shipping_api_get',
        'woocommerce_api',
        'backend_api',
        'backend_api_service',
        'cubs_api',
        'allow_sendgrid',
        'sendgrid_name',
        'sendgrid_email',
        'sendgrid_url',
        'sendgrid_auto_installer_allow',
        'sendgrid_order_created_allow',
        'sendgrid_order_created_id',
        'sendgrid_order_shipped_allow',
        'sendgrid_order_shipped_id',
    ];

    public function users(): BelongsToMany {
        return $this->belongsToMany(User::class);
    }

    public function company(): BelongsTo {
        return $this->belongsTo(Company::class);
    }

    public function inventories(): HasMany {
        return $this->hasMany(Inventory::class)
            ->where('team_id', '=', $this->id)
            ->orWhere('global', '=', true);
    }

    public function orders(): HasMany {
        return $this->hasMany(Order::class);
    }

    public function chargers(): HasMany {
        return $this->hasMany(Charger::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function installations(): HasMany {
        return $this->hasMany(Installation::class);
    }

    public function allowSend(): bool {
        return $this->shipping_api_send;
    }

    public function purchase_orders(): HasMany {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function pipelines(): HasMany {
        return $this->hasMany(Pipeline::class);
    }

    public function returnOrders(): HasMany {
        return $this->hasMany(ReturnOrder::class);
    }

}
