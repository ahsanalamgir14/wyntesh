<?php

namespace App\Listeners;

use App\Events\GeneratePayoutEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Admin\Income;
use App\Models\Admin\IncomeParameter;
use App\Models\Admin\Sale;
use App\Models\Admin\Member;
use App\Models\Admin\MembersLegPv;
use App\Models\Admin\Rank;
use App\Models\Admin\RankLog;
use App\Models\Admin\CompanySetting;
use App\Models\Admin\Payout;
use App\Models\Admin\PayoutIncome;
use App\Models\Admin\MemberPayout;
use App\Models\Admin\MemberPayoutIncome;
use App\Models\Admin\MemberIncomeHolding;
use App\Models\Admin\WalletTransaction;
use App\Models\Superadmin\TransactionType;
use App\Http\Controllers\User\MembersController;
use Illuminate\Support\Facades\Log;
use DB;
use Carbon\Carbon;
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
        $this->updateRank($payout);
        //Get Incomes of Payout
        $income_ids=PayoutIncome::where('payout_id',$payout->id)->get()->pluck('income_id');
        dd($income_ids);
       

        // Get Income Ids of Payout 
        $Incomes=Income::whereIn('id',$income_ids)->get();
        
        foreach ($Incomes as $income) {

            // Get Payout income from payout id
            $PayoutIncome=PayoutIncome::where('payout_id',$payout->id)->where('income_id',$income->id)->first();
            if($income->code=='MATACHING'){

                $percent_of_total_company_bv=0;

                foreach ($income->income_parameters as $parameter) {
                    // Get parameter for matching bonus income.
                    
                    if($parameter->name=='percent_of_total_company_bv'){
                        $percent_of_total_company_bv=$parameter->value_1;
                    }  
                    
                    
                }

                // Counting matching point value based on parameters and plan criteria
                $PayoutIncome->income_payout_parameter_1_name='matching_point_value';
                if($payout->total_matched_bv==0){
                    $matching_point_value=0;
                }else{
                    $matching_point_value=(($payout->sales_bv*$percent_of_total_company_bv)/100)/$payout->total_matched_bv;    
                }
                
                $PayoutIncome->income_payout_parameter_1_value=round($matching_point_value,4);
                $PayoutIncome->save();
            }

        }
    }
     public function updateRank($payout){
        $Members=Member::orderBy('level','desc')->get();
        $Ranks=Rank::all();
        $MembersController=new MembersController;
        foreach ($Members as $Member) {
            $group_pv=MembersLegPv::where('member_id',$Member->id)->sum('pv');
            $children=Member::where('parent_id',$Member->id)->get()->pluck('id')->toArray();
            $counts=array();
            
            foreach ($children as $child) {
                $child_ids=$MembersController->getChildsOfParent($child);
                $child_ids[]=$child;

               $check_rank=Member::whereIn('id',$child_ids)->get()->pluck('rank_id')->toArray();
               //if($Member->id==1)
                
                $check_rank=array_unique($check_rank);
              foreach ($check_rank as $check) {
                        $counts[]=$check;
               }                           
            }
            
            $counts=array_count_values($counts);

            foreach ($Ranks as $Rank) {
               
                if($Rank->bv_to){
                    if($group_pv >= $Rank->bv_from ){
                       
                        $Member->rank_id=$Rank->id;
                        $Member->save();
                    }

                }else if($Rank->leg_rank){
                                     
                    foreach ($counts as $key => $value) {   
                        if($Rank->leg_rank===$key && $Rank->leg_rank_count == $value){                           
                            $Member->rank_id=$Rank->id;
                            $Member->save();   
                        }
                    }

                }

            } 
            
            $RankLog=new RankLog;
            $RankLog->payout_id=$payout->id;
            $RankLog->member_id=$Member->id;
            $RankLog->rank_id=$Member->rank_id;
            $RankLog->save();
        }
    }
}
