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
        $Members=Member::orderBy('level','desc')->get();
        $sales_start_date=$Payout->sales_start_date;
        $sales_end_date=$Payout->sales_end_date;

        foreach ($Members as $Member) {
            $member_total_bv=Sale::whereBetween('created_at', [$Payout->sales_start_date, $Payout->sales_end_date])->where('member_id',$Member->id)->sum('pv');

            $path=$Member->path;
            $position=$Member->position;

            $uplines=explode('/', $path);        
            $uplines=array_reverse($uplines);
            $uplines=array_filter($uplines, 'strlen');            
            array_shift($uplines);

            foreach ($uplines as $upline) {
                $MembersLegPv=MembersLegPv::where('member_id',$upline)->where('position',$position)->first();
                $UplineMember=Member::where('id',$upline)->first();
              
                if($MembersLegPv){                    
                    $MembersLegPv->current_pv+=$member_total_bv;
                    $MembersLegPv->total_pv+=$member_total_bv;
                    $MembersLegPv->save();
                }else{
                    $MembersLegPv=new MembersLegPv;
                    $MembersLegPv->member_id=$upline;
                    $MembersLegPv->position=$position;
                    $MembersLegPv->current_pv=$member_total_bv;
                    $MembersLegPv->total_pv=$member_total_bv;
                    $MembersLegPv->save();
                }
                $position=$UplineMember->position;
            }
        }

        event(new GeneratePayoutEvent($Payout));

    }
}
