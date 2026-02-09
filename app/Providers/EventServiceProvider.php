<?php

namespace App\Providers;

use App\Events\AppointmentStateChanged;
use App\Listeners\ManageQueueOnStateChange;
use App\Listeners\SendWhatsAppOnStateChange;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        AppointmentStateChanged::class => [
            ManageQueueOnStateChange::class,
            SendWhatsAppOnStateChange::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
