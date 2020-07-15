<?php

namespace App\Listeners;

use App\Events\GenerateMonthlyPayoutEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Admin\Income;
use App\Models\Admin\IncomeParameter;
use App\Models\Admin\Sale;
use App\Models\Admin\Member;
use App\Models\Admin\MembersLegPv;
use App\Models\Admin\Payout;
use App\Models\Admin\PayoutIncome;
use App\Models\Admin\MemberPayout;
use App\Models\Admin\MemberPayoutIncome;
use App\Models\Admin\WalletTransaction;
use App\Models\Superadmin\TransactionType;
use App\Events\GeneratePayoutEvent;

class GenerateMonthlyPayoutListener
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
     * @param  GenerateMonthlyPayoutEvent  $event
     * @return void
     */    

    public function handle(GenerateMonthlyPayoutEvent $event)
    {
        $Payout=$event->payout;
        

        event(new GeneratePayoutEvent($Payout));

    }
}
