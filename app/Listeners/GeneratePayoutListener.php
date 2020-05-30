<?php

namespace App\Listeners;

use App\Events\GeneratePayoutEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Admin\Income;
use App\Models\Admin\Sale;
use App\Models\Admin\Member;
use App\Models\Admin\MemberCarryForwardPv;
use App\Models\Admin\Payout;
use App\Models\Admin\PayoutIncome;
use App\Models\Admin\MemberPayout;
use App\Models\Admin\MemberPayoutIncome;

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
     * @param  GeneratePayoutEvent  $event
     * @return void
     */
    public function handle(GeneratePayoutEvent $event)
    {
       $payout=$event->payout;
    }
}
