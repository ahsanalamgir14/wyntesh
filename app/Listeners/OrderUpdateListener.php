<?php

namespace App\Listeners;

use App\Events\OrderUpdateEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrderUpdateNotification;

use App\Http\Controllers\Admin\PayoutsController;

class OrderUpdateListener implements ShouldQueue
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
     * @param  OrderUpdateEvent  $event
     * @return void
     */
    public function handle(OrderUpdateEvent $event)
    {
        $order=$event->order;
        $user=$event->user;
        if($order->delivery_status=='Order Confirmed'){
            $PayoutsController=new PayoutsController;
            if($order->payout_id){
                $PayoutsController->releaseHoldPayout($order->payout_id,$user);    
            }            
        }
        Notification::send($user, new OrderUpdateNotification($order));
    }    
}
