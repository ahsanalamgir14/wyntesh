<?php

namespace App\Listeners;

use App\Events\GeneratePayoutEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Admin\Income;
use App\Models\Admin\Sale;
use App\Models\Admin\Member;
use App\Models\Admin\MembersLegPv;
use App\Models\Admin\Payout;
use App\Models\Admin\PayoutIncome;
use App\Models\Admin\MemberPayout;
use App\Models\Admin\MemberPayoutIncome;

use Illuminate\Support\Facades\Log;

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
        $sales_amount=Sale::whereBetween('created_at', [$payout->sales_start_date, $payout->sales_end_date])->sum('final_amount_company');
        $total_bv=Sale::whereBetween('created_at', [$payout->sales_start_date, $payout->sales_end_date])->sum('pv');

        $Members=Member::whereHas('user',function($q){
            $q->where('is_active',1);
        })->get();

        foreach ($Members as $Member) {
            $matched_pv=0;
            $carry_forward=0;
            $leg_1_pv=0;
            $leg_2_pv=0;
            $legs=MembersLegPv::where('member_id',$Member->id)->orderBy('current_pv','desc')->get();
            foreach ($legs as $key => $leg) {
                if($key==0){
                    $leg_1_pv=$leg->current_pv;
                    continue;
                }

                if($key==1){
                    $leg_2_pv=$leg->current_pv;
                    $carry_forward=$leg_1_pv-$leg_2_pv;
                }

                $matched_pv+=$leg->current_pv;
            }

            Log::info('Member: '.$Member->user->name.', Mached: '.$matched_pv.',Carry forward: '.$carry_forward);
        }
    }
}
