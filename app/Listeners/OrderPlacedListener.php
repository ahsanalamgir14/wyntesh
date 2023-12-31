<?php

namespace App\Listeners;

use App\Events\OrderPlacedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrderPlaced as OrderPlacedNotification;

class OrderPlacedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderPlacedEvent  $event
     * @return void
     */
    public function handle(OrderPlacedEvent $event)
    {
        $order=$event->order;
        $user=$event->user;
        Notification::send($user, new OrderPlacedNotification($order));

    }
}
