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
use App\Models\Admin\MemberCarryForwardPv;
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
        //$this->updateRank($payout);
        //Get Incomes of Payout
        $income_ids=PayoutIncome::where('payout_id',$payout->id)->get()->pluck('income_id');

         //Get total Sales amount/ Total BV Turnover of duration
        $sales_amount=Sale::whereDate('created_at','<=', $payout->sales_end_date)
                            ->whereDate('created_at','>=', $payout->sales_start_date)
                            ->sum('final_amount_company');

        $total_bv=Sale::whereBetween('created_at', [$payout->sales_start_date, $payout->sales_end_date])->sum('pv');
      


        $Members=Member::whereHas('user',function($q){
            $q->where('is_active',1);
        })->get();
        // dd($Members);
        $total_mached_bv=0;
        $total_carry_forward_bv=0;

        foreach ($Members as $Member) {

            // Personal Sales amount and BV of Member
            $member_sales_amount=Sale::whereDate('created_at','<=',$payout->sales_end_date)
                                ->whereDate('created_at','>=',$payout->sales_start_date)
                                ->where('member_id',$Member->id)->sum('final_amount_company');

            $member_total_bv=Sale::whereDate('created_at','<=',$payout->sales_end_date)
                                    ->whereDate('created_at','>=',$payout->sales_start_date)
                                    ->where('member_id',$Member->id)->sum('pv');

        
            // Personal Sales amount and BV of Group/Legs
            $member_leg_sales_amount=Sale::whereDate('created_at','<=',$payout->sales_end_date)
                                    ->whereDate('created_at','>=',$payout->sales_start_date)
                                    ->whereIn('member_id',$Member->children->pluck('id'))->sum('final_amount_company');

            $member_leg_total_bv=Sale::whereDate('created_at','<=',$payout->sales_end_date)
                                ->whereDate('created_at','>=',$payout->sales_start_date)
                                ->whereIn('member_id',$Member->children->pluck('id'))->sum('pv');

            // Entry in Member payout
            $MemberPayout=new MemberPayout;
            $MemberPayout->member_id=$Member->id;
            $MemberPayout->payout_id=$payout->id;
            $MemberPayout->sales_pv=$member_total_bv;
            $MemberPayout->sales_amount=$member_sales_amount;
            $MemberPayout->group_sales_pv=$member_leg_total_bv;
            $MemberPayout->group_sales_amount=$member_leg_sales_amount;
            $MemberPayout->total_payout=0;
            $MemberPayout->save();

            $matched_bv=0;
            $carry_forward=0;
            $carry_forward_position=0;
            $leg_1_pv=0;
            $leg_2_pv=0;

            //Counting Carry forward and Matched points of Member Legs.

            //Getting Member Legs in decenting based on current PV
            // $legs=MembersLegPv::where('member_id',$Member->id)->orderBy('pv','desc')->get();
            

            $legs= MembersLegPv::addSelect(['*', \DB::raw('sum(pv) as totalPv')])
                        ->whereDate('created_at','<=',$payout->sales_end_date)
                        ->whereDate('created_at','>=',$payout->sales_start_date)
                        ->where('member_id',$Member->id)
                        ->orderBy('totalPv','desc')
                        ->groupBy('position')
                        ->get();
           
           
            foreach ($legs as $key => $leg) {
                if($key==0){
                    $leg_1_pv=$leg->totalPv;
                    $carry_forward_position=$leg->position;

                    // If only 1 leg then no matching bonus, carry forward all current pv
                    if($legs->count()==1){
                        $carry_forward=$leg->totalPv;                        
                    }
                    continue;
                }

                if($key==1){
                    $leg_2_pv=$leg->totalPv;
                    // Count carry forward
                    $carry_forward=$leg_1_pv-$leg_2_pv;
                }
                            
                // Add current pv to matched_bv of all legs except strong one.
                $matched_bv+=$leg->totalPv;
            }
            // dd($carry_forward);
            if(!$legs->isEmpty()){
                $MemberCarryForwardPv=new MemberCarryForwardPv;
                $MemberCarryForwardPv->member_id            =$Member->id;
                $MemberCarryForwardPv->payout_id            =$payout->id;
                $MemberCarryForwardPv->position             =$carry_forward_position;
                $MemberCarryForwardPv->pv                   =$carry_forward;
                $MemberCarryForwardPv->save();
            }
         
            // Count total of all values;
            $matched_bv = floatval($matched_bv)/24;
            $total_mached_bv+=$matched_bv;
            $total_carry_forward_bv+=$carry_forward;
            
            // Save Matched bv and total carry_forward to member payout.
            $MemberPayout->total_matched_bv=$matched_bv;
            $MemberPayout->total_carry_forward_bv=$carry_forward;
            $MemberPayout->save();

        }

        // Save total values to payouts.
        $payout->sales_bv=$total_bv;
        $payout->sales_amount=$sales_amount;
        $payout->total_matched_bv=$total_mached_bv;
        $payout->total_carry_forward_bv=$total_carry_forward_bv;
        $payout->save();

        // Get Income Ids of Payout 
        $Incomes=Income::whereIn('id',$income_ids)->get();
        
        foreach ($Incomes as $income) {

            // Get Payout income from payout id
            $PayoutIncome=PayoutIncome::where('payout_id',$payout->id)->where('income_id',$income->id)->first();
            if($income->code=='SQUAD'){

                $weekly_company_turnover_percent=0;

                foreach ($income->income_parameters as $parameter) {
                    // Get parameter for matching bonus income.
                    
                    if($parameter->name=='weekly_company_turnover_percent'){
                        $weekly_company_turnover_percent=$parameter->value_1;
                    }  
                }

                // Counting matching point value based on parameters and plan criteria
                $PayoutIncome->income_payout_parameter_1_name='sbp';
                if($payout->total_matched_bv==0){
                    $income_factor=0;
                }else{
                    $income_factor=(($payout->sales_bv*$weekly_company_turnover_percent)/100)/$payout->total_matched_bv;    
                }
                
                $PayoutIncome->income_payout_parameter_1_value=round($income_factor,4);
                $PayoutIncome->save();
            }

        }


        foreach ($Members as $Member) {
            $memberPayout = MemberPayout::where('member_id',$Member->id)->where('payout_id',$payout->id)->first();
            $Incomes = Income::whereIn('id',$income_ids)->get();
            $totalIncomeValue = "";
            foreach($Incomes as $income){
                if($income->code=='SQUAD'){
                    $payoutIcome = PayoutIncome::where('payout_id',$payout->id)->where('income_id',$income->id)->first();
                    $factor = $payoutIcome->income_payout_parameter_1_value;
                    $totalIncomeValue = $memberPayout->total_matched_bv*$factor; 
                }
                if($totalIncomeValue!="0.0"){
                    $MemberPayoutIncome = new MemberPayoutIncome;
                    $MemberPayoutIncome->member_id                              = $Member->id;
                    $MemberPayoutIncome->payout_id                              = $payout->id;
                    $MemberPayoutIncome->income_id                              = $income->id;
                    $MemberPayoutIncome->payout_amount                          = $totalIncomeValue;
                    $MemberPayoutIncome->income_payout_parameter_1_name         = $income->income_payout_parameter_1_name;
                    $MemberPayoutIncome->income_payout_parameter_1_value        = $income->income_payout_parameter_1_value;
                    $MemberPayoutIncome->save();
                }
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
