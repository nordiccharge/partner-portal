<?php

namespace App\Providers;

use App\Events\OrderCreated;
use App\Events\OrderFulfilled;
use App\Events\PushOrderToShipping;
use App\Events\SendEmailToInstaller;
use App\Events\TicketCreated;
use App\Listeners\AssignOrderInstaller;
use App\Listeners\CreateOrderInvoice;
use App\Listeners\SendInstallationEmail;
use App\Listeners\SendOrderCreatedEmail;
use App\Listeners\SendOrderCreatedNotification;
use App\Listeners\SendOrderFulfilledNotification;
use App\Listeners\SendTicketCreatedNotification;
use App\Models\InstallerPostal;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnOrder;
use App\Observers\CommentObserver;
use App\Observers\InstallerPostalObserver;
use App\Observers\InvoiceItemObserver;
use App\Observers\InvoiceObserver;
use App\Observers\OrderItemObserver;
use App\Observers\OrderObserver;
use App\Observers\ReturnOrderObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

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
            AssignOrderInstaller::class,
            SendOrderCreatedNotification::class,
            SendOrderCreatedEmail::class,
            SendInstallationEmail::class,
            CreateOrderInvoice::class
        ],
        OrderFulfilled::class => [
            SendOrderFulfilledNotification::class
        ],
        TicketCreated::class => [
            SendTicketCreatedNotification::class
        ],
        SendEmailToInstaller::class => [
            SendInstallationEmail::class
        ],
        PushOrderToShipping::class => [
            SendOrderCreatedNotification::class
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        OrderItem::observe(OrderItemObserver::class);
        InstallerPostal::observe(InstallerPostalObserver::class);
        ReturnOrder::observe(ReturnOrderObserver::class);
        Order::observe(OrderObserver::class);
        Invoice::observe(InvoiceObserver::class);
        InvoiceItem::observe(InvoiceItemObserver::class);
    }


    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
