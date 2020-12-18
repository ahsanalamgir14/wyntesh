<?php

namespace App\Listeners;

use App\Events\GeneratePayoutEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Classes\PayoutHandler;

class GeneratePayoutListener
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
     * @param  GenerateLevelPayoutEvent  $event
     * @return void
     */
    public function handle(GeneratePayoutEvent $event)
    {
        $payout=$event->payout;
        $PayoutHandler=new PayoutHandler($payout);
        $PayoutHandler->calculatePayout();                
    }

}
