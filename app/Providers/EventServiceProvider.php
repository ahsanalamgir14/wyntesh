<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\OrderPlacedEvent' => [
            'App\Listeners\OrderPlacedListener',
        ],
        'App\Events\OrderUpdateEvent' => [
            'App\Listeners\OrderUpdateListener',
        ],
        'App\Events\UpdateGroupPVEvent' => [
            'App\Listeners\UpdateGroupPVListener',
        ],
        'App\Events\UpdateGroupPVEvent' => [
            'App\Listeners\UpdateGroupPVListener',
        ],
        'App\Events\GeneratePayoutEvent' => [
            'App\Listeners\GeneratePayoutListener',
        ],
        'App\Events\MemberRegisteredEvent' => [
            'App\Listeners\MemberRegisteredListener',
        ],

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
