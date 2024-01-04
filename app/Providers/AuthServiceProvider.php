<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Charger;
use App\Models\Installation;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Policies\ChargerPolicy;
use App\Policies\InstallationPolicy;
use App\Policies\InventoryPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PurchaseOrderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Order::class => OrderPolicy::class,
        Charger::class => ChargerPolicy::class,
        Inventory::class => InventoryPolicy::class,
        Installation::class => InstallationPolicy::class,
        PurchaseOrder::class => PurchaseOrderPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
