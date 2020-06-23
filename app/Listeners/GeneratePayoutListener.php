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
        $this->updateRank();
        //Get Incomes of Payout
        $income_ids=PayoutIncome::where('payout_id',$payout->id)->get()->pluck('income_id');
        
        //Get total Sales amount/ Total BV Turnover of duration
        $sales_amount=Sale::whereBetween('created_at', [$payout->sales_start_date, $payout->sales_end_date])->sum('final_amount_company');
        $total_bv=Sale::whereBetween('created_at', [$payout->sales_start_date, $payout->sales_end_date])->sum('pv');

        $Members=Member::whereHas('user',function($q){
            $q->where('is_active',1);
        })->get();

        $total_mached_bv=0;
        $total_carry_forward_bv=0;

        foreach ($Members as $Member) {

            // Personal Sales amount and BV of Member
            $member_sales_amount=Sale::whereBetween('created_at', [$payout->sales_start_date, $payout->sales_end_date])->where('member_id',$Member->id)->sum('final_amount_company');
            
            // total bv turnover including withholding bv
            $member_total_bv=Sale::whereBetween('created_at', [$payout->sales_start_date, $payout->sales_end_date])->where('member_id',$Member->id)->sum('pv');

            // Personal Sales amount and BV of Group/Legs
            $member_leg_sales_amount=Sale::whereBetween('created_at', [$payout->sales_start_date, $payout->sales_end_date])->whereIn('member_id',$Member->children->pluck('id'))->sum('final_amount_company');
            $member_leg_total_bv=Sale::whereBetween('created_at', [$payout->sales_start_date, $payout->sales_end_date])->whereIn('member_id',$Member->children->pluck('id'))->sum('pv');

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
            $legs=MembersLegPv::where('member_id',$Member->id)->orderBy('current_pv','desc')->get();
            foreach ($legs as $key => $leg) {
                if($key==0){
                    $leg_1_pv=$leg->current_pv;
                    $carry_forward_position=$leg->position;

                    // If only 1 leg then no matching bonus, carry forward all current pv
                    if($legs->count()==1){
                        $carry_forward=$leg->current_pv;                        
                        // Reset Leg Current Pv to Carry Forward;
                        $leg->current_pv=0;
                        $leg->save();
                    }
                    continue;
                }

                if($key==1){                    
                    $leg_2_pv=$leg->current_pv;

                    // Count carry forward
                    $carry_forward=$leg_1_pv-$leg_2_pv;
                }
                            
                // Add current pv to matched_bv of all legs except strong one.
                $matched_bv+=$leg->current_pv;

                // Reset Leg Current Pv to Carry Forward;
                $leg->current_pv=0;
                $leg->save();
            }

            
            // Carry Forward PV in leg 1
            $leg1=MembersLegPv::where('member_id',$Member->id)->where('position',$carry_forward_position)->first();
            if($leg1){
                $leg1->current_pv=$carry_forward;
                $leg1->save();
            }
            // Count total of all values;
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
            }else{
                $percent_of_total_company_bv=0;
                $matching_pv=0;

                foreach ($income->income_parameters as $parameter) {
                    // Get parameter for income.                    
                    if($parameter->name=='percent_of_total_company_bv'){
                        $percent_of_total_company_bv=$parameter->value_1;
                    }
                    if($parameter->name=='matching_pv'){
                        $percent_of_total_company_bv=$parameter->value_1;
                    }                                          
                }

                // matching point value calculation for various income
                if($income->code=='CONSISTENCY'){
                    $Members_Matched=$MemberPayout::where('payout_id',$payout->id)->where('total_matched_bv','>=',$matching_pv)->get();

                    $PayoutIncome->income_payout_parameter_1_name='matching_point_value';
                    if($payout->total_matched_bv==0){
                        $matching_point_value=0;
                    }else{
                        $matching_point_value=(($payout->sales_bv*$percent_of_total_company_bv)/100)/$payout->total_matched_bv;    
                    }
                    $PayoutIncome->income_payout_parameter_1_value=round($matching_point_value,4);
                    $PayoutIncome->save();
                }else{
                    $quilifier_matched_pv=0;
                    if($income->code=='TRIP_ALL' || $income->code=='VEHICLE_ALL' || $income->code=='HOUSE_ALL' || $income->code=='SUPER_GROWTH_ALL'){

                        // Get all qualifier whose rank is greator than or equals to gold and satisfies matching pv condtion

                        $quilifier_matched_pv=$MemberPayout::where('payout_id',$payout->id)
                        ->whereHas('member.rank',function($q){
                            $q->where('id','>=',[6]);
                        })
                        ->where('total_matched_bv','>=',$matching_pv)->sum('total_matched_bv');
                    }

                    if($income->code=='TRIP_DIA_EXE' || $income->code=='VEHICLE_DIA_EXE' || $income->code=='HOUSE_DIA_EXE' || $income->code=='SUPER_GROWTH_DIA_EXE'){

                        // Get all qualifier whose rank is greator than or equals to diamond and satisfies matching pv condtion
                        $quilifier_matched_pv=$MemberPayout::where('payout_id',$payout->id)
                        ->whereHas('member.rank',function($q){
                            $q->where('id','>=',[7]);
                        })
                        ->where('total_matched_bv','>=',$matching_pv)->sum('total_matched_bv');
                    }

                    if($income->code=='TRIP_DIPLOMAT' || $income->code=='VEHICLE_DIPLOMAT' || $income->code=='HOUSE_DIPLOMAT' || $income->code=='SUPER_GROWTH_DIPLOMAT'){

                        // Get all qualifier whose rank is greator than or equals to diplomat and satisfies matching pv condtion
                        $quilifier_matched_pv=$MemberPayout::where('payout_id',$payout->id)
                        ->whereHas('member.rank',function($q){
                            $q->where('id','>=',[8]);
                        })
                        ->where('total_matched_bv','>=',$matching_pv)->sum('total_matched_bv');
                    }

                    if($income->code!='FRANCHISE'){
                        $PayoutIncome->income_payout_parameter_1_name='matching_point_value';
                        if($quilifier_matched_pv==0){
                            $matching_point_value=0;
                        }else{
                            // calculate matching point value. (factor)
                            $matching_point_value=(($payout->sales_bv*$percent_of_total_company_bv)/100)/$quilifier_matched_pv;    
                        }
                        $PayoutIncome->income_payout_parameter_1_value=round($matching_point_value,4);
                        $PayoutIncome->save();
                    }

                    if($income->code=='FRANCHISE'){
                        
                        if($Member->rank_id <= 6){
                            $rank=Rank::find(6);
                        }else if($Member->rank_id == 7){
                            $rank=Rank::find(8);
                        }else if($Member->rank_id >= 8){
                            $rank=Rank::find(8);
                        }

                        $IncomeParameter=IncomeParameter::where('name',$rank->name)->where('income_id',$income->id)->first();                        
                        $PayoutIncome->income_payout_parameter_1_name='franchise_bonus_percent';
                        $PayoutIncome->income_payout_parameter_1_value=$IncomeParameter->value_1;
                        $PayoutIncome->save();
                    }

                    

                }
            }
        }

        $all_income_payout_total=0;
        // Calculating Member Payout Amount

        $Members=Member::whereHas('user',function($q){
            $q->where('is_active',1);
        })->orderBy('level','desc')->get();

        foreach ($Members as $Member) {
            // Get Incomes included in payout.
            $total_payout=0;
            $PayoutIncomes=PayoutIncome::where('payout_id',$payout->id)->get();
            $MemberPayout= MemberPayout::where('member_id',$Member->id)->where('payout_id',$payout->id)->first();
            // get member total puchase in payout period to check minimum bv condition based on rank.
            $toal_bv_without_withhold_bv=Sale::whereBetween('created_at', [$MemberPayout->payout->sales_start_date, $MemberPayout->payout->sales_end_date])->where('member_id',$Member->id)->where('is_withhold_purchase',0)->sum('pv');
           
            foreach ($PayoutIncomes as $PayoutIncome) {
                // Count payout based on income.
                if($PayoutIncome->income_payout_parameter_1_name=='matching_point_value'){
                    // MemberIncomePayout Calculation
                    $income_payout_amount=$MemberPayout->total_matched_bv*$PayoutIncome->income_payout_parameter_1_value;

                    if($PayoutIncome->income->code=='MATACHING'){
                        
                        $payout_amount=$MemberPayout->total_matched_bv*$PayoutIncome->income_payout_parameter_1_value;

                        $capping=$Member->rank->capping;
                        if($payout_amount>$capping){
                            $payout_amount=$capping;
                        }

                        if($payout_amount==0){
                            continue;
                        }

                        $TransactionType=TransactionType::where('name','Matching Bonus')->first();
                        $MemberPayoutIncome=new MemberPayoutIncome;
                        $MemberPayoutIncome->payout_id=$payout->id;
                        $MemberPayoutIncome->income_id=$PayoutIncome->income_id;
                        $MemberPayoutIncome->member_id=$Member->id;
                        $MemberPayoutIncome->income_payout_parameter_1_name='matching_point_value';
                        $MemberPayoutIncome->income_payout_parameter_1_value=$PayoutIncome->income_payout_parameter_1_value;
                        $MemberPayoutIncome->payout_amount=$payout_amount;
                        $MemberPayoutIncome->save();
                    }

                    if($PayoutIncome->income->code=='CONSISTENCY'){
                       
                        if($income_payout_amount==0){
                            continue;
                        }

                        $payout_amount=$MemberPayout->total_matched_bv*$PayoutIncome->income_payout_parameter_1_value;

                        $rank=$Member->rank->name;
                        $IncomeParameter=IncomeParameter::where('income_id',$PayoutIncome->income_id)->where('name',$rank)->first();

                        if($payout_amount>$IncomeParameter->value_1){
                            $payout_amount=$IncomeParameter->value_1;
                        }

                        $TransactionType=TransactionType::where('name','Consistency Bonus')->first();
                        $MemberPayoutIncome=new MemberPayoutIncome;
                        $MemberPayoutIncome->payout_id=$payout->id;
                        $MemberPayoutIncome->income_id=$PayoutIncome->income_id;
                        $MemberPayoutIncome->member_id=$Member->id;
                        $MemberPayoutIncome->income_payout_parameter_1_name='matching_point_value';
                        $MemberPayoutIncome->income_payout_parameter_1_value=$PayoutIncome->income_payout_parameter_1_value;
                        $MemberPayoutIncome->payout_amount=$payout_amount;
                        $MemberPayoutIncome->save();
                    }else{
                        if($income_payout_amount==0){
                            continue;
                        }

                        $TransactionType=TransactionType::where('name','Achievers’s Fund')->first();
                        $MemberPayoutIncome=new MemberPayoutIncome;
                        $MemberPayoutIncome->payout_id=$payout->id;
                        $MemberPayoutIncome->income_id=$PayoutIncome->income_id;
                        $MemberPayoutIncome->member_id=$Member->id;
                        $MemberPayoutIncome->income_payout_parameter_1_name='matching_point_value';
                        $MemberPayoutIncome->income_payout_parameter_1_value=$PayoutIncome->income_payout_parameter_1_value;
                        $MemberPayoutIncome->payout_amount=($MemberPayout->total_matched_bv*$PayoutIncome->income_payout_parameter_1_value);
                        $MemberPayoutIncome->save();
                    }                                        
                }else if($PayoutIncome->income_payout_parameter_1_name=='franchise_bonus_percent'){
                    if($PayoutIncome->income->code=='FRANCHISE'){
                        $TransactionType=TransactionType::where('name','Franchise Bonus')->first();
                        $MemberPayoutIncome=new MemberPayoutIncome;
                        $MemberPayoutIncome->payout_id=$payout->id;
                        $MemberPayoutIncome->income_id=$PayoutIncome->income_id;
                        $MemberPayoutIncome->member_id=$Member->id;
                        $MemberPayoutIncome->income_payout_parameter_1_name='franchise_bonus_percent';
                        $MemberPayoutIncome->income_payout_parameter_1_value=$PayoutIncome->income_payout_parameter_1_value;                                                
                        $sponsored=$Member->sponsored->pluck('id')->toArray();
                        $total_sponsor_payout=0;
                        foreach ($sponsored as $sponsor) {
                            $SponsorPayout=MemberPayout::where('member_id',$sponsor)->where('payout_id',$payout->id)->first();
                            if($SponsorPayout->total_payout!=0){
                                $total_sponsor_payout+=($SponsorPayout->total_payout*$PayoutIncome->income_payout_parameter_1_value)/100;
                            }

                        }

                        if($total_sponsor_payout==0){
                            continue;
                        }
                        $income_payout_amount=$total_sponsor_payout;
                        $MemberPayoutIncome->payout_amount=$total_sponsor_payout;
                        $MemberPayoutIncome->save();
                    }
                }

                if($TransactionType){
                    if($Member->rank->personal_bv_condition > $toal_bv_without_withhold_bv){
                        if($MemberPayoutIncome->payout_amount != 0){
                            $MemberIncomeHolding=new MemberIncomeHolding;
                            $MemberIncomeHolding->member_id=$Member->id;
                            $MemberIncomeHolding->payout_id=$payout->id;
                            $MemberIncomeHolding->income_id=$MemberPayoutIncome->income_id;
                            $MemberIncomeHolding->rank_id=$Member->rank_id;
                            $MemberIncomeHolding->rank_bv_criteria=$Member->rank->personal_bv_condition;
                            $MemberIncomeHolding->current_bv=$toal_bv_without_withhold_bv;
                            $MemberIncomeHolding->required_bv=$Member->rank->personal_bv_condition-$toal_bv_without_withhold_bv;
                            $MemberIncomeHolding->amount=$MemberPayoutIncome->payout_amount;
                            $MemberIncomeHolding->is_paid=0;
                            $MemberIncomeHolding->save();
                        }                            
                    }else{
                        if($MemberPayoutIncome->payout_amount != 0){
                            $WalletTransaction=new WalletTransaction;
                            $WalletTransaction->member_id=$Member->id;
                            $WalletTransaction->amount=$MemberPayoutIncome->payout_amount;
                            $WalletTransaction->balance=$MemberPayoutIncome->payout_amount+$Member->wallet_balance;
                            $WalletTransaction->transaction_type_id=$TransactionType->id;
                            $WalletTransaction->transfered_to=$Member->user->id;
                            $WalletTransaction->note='Payout Income';
                            $WalletTransaction->save(); 
                        }
                    }
                    
                }
                // Getting Total Payout of all incomes for member.                
                $total_payout+=$income_payout_amount;
            }

            $MemberPayout->total_payout=$total_payout;
            $MemberPayout->save();
            $Member->current_personal_pv=0;
            $Member->wallet_balance+=$total_payout;
            $Member->save();
            // calculating total payout for this payout run
            $all_income_payout_total+=$total_payout;
        }

        // Calculating income wise total payout.
        foreach ($PayoutIncomes as $PayoutIncome) {
            $TotalIncomePayout= MemberPayoutIncome::where('payout_id',$PayoutIncome->payout_id)->where('income_id',$PayoutIncome->income_id)->sum('payout_amount');
            $PayoutIncome->payout_amount=$TotalIncomePayout;
            $PayoutIncome->save();
        }

        // Saving total payout to payout
        $payout->total_payout=$all_income_payout_total;
        $payout->save();

    }

    public function updateRank(){
        $Members=Member::orderBy('level','desc')->get();
        $Ranks=Rank::all();
        $MembersController=new MembersController;
        foreach ($Members as $Member) {
            $group_pv=MembersLegPv::where('member_id',$Member->id)->sum('total_pv');
            $children=Member::where('parent_id',$Member->id)->get()->pluck('id')->toArray();
            $counts=array();
            
            foreach ($children as $child) {
                $child_ids=$MembersController->getChildsOfParent($child);
                $child_ids[]=$child;

               $check_rank=Member::whereIn('id',$child_ids)->get()->pluck('rank_id')->toArray();
               if($Member->id==1)
                
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
            
            
        }
    }
}
