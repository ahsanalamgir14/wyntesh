<?php

namespace App\Listeners;

use App\Events\GeneratePayoutEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Admin\CompanySetting;
use App\Classes\PayoutHandler;

class GeneratePayoutListener
{
    public $is_automatic_payout=0;

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
        $this->is_automatic_payout=CompanySetting::getValue('is_automatic_payout');
        if($this->is_automatic_payout == 1) {
            $PayoutHandler->calculatePayout();
        }
    }

}
