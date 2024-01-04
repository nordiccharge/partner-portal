<?php

namespace App\Providers;

use App\Events\OrderCreated;
use App\Events\OrderFulfilled;
use App\Http\Controllers\API\OrderController;
use App\Listeners\SendOrderCreatedEmail;
use App\Listeners\SendOrderCreatedNotification;
use App\Listeners\SendOrderFulfilledNotification;
use App\Models\InstallerPostal;
use App\Models\Order;
use App\Models\OrderItem;
use App\Observers\InstallerPostalObserver;
use App\Observers\OrderItemObserver;
use App\Observers\OrderObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        OrderCreated::class => [
            SendOrderCreatedNotification::class,
            SendOrderCreatedEmail::class
        ],
        OrderFulfilled::class => [
            SendOrderFulfilledNotification::class
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        OrderItem::observe(OrderItemObserver::class);
        InstallerPostal::observe(InstallerPostalObserver::class);
    }


    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
