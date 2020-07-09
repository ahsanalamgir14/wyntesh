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
        
        //Get total Sales amount/ Total BV Turnover of duration
        $sales_amount=Sale::whereBetween('created_at', [$payout->sales_start_date, $payout->sales_end_date])->sum('final_amount_company');
        $total_bv=Sale::whereBetween('created_at', [$payout->sales_start_date, $payout->sales_end_date])->sum('pv');

        $Members=Member::whereHas('user',function($q){
            $q->where('is_active',1);
        })->get();

        $tds_percentage=CompanySetting::getValue('tds_percentage');
        $admin_fee_percent=CompanySetting::getValue('admin_fee_percent');
        $tds_percentage=$tds_percentage?$tds_percentage:0;
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
            $MemberPayout->tds=0;
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
            $Member->total_matched_bv+=$matched_bv;
            $Member->save();
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
                        $matching_pv=$parameter->value_1;
                    }                                          
                }

                // matching point value calculation for various income
                if($income->code=='CONSISTENCY'){
                    
                    // $booster_ids=Member::whereBetween('created_at',[$payout->sales_start_date,$payout->sales_end_date])->get()->pluck('id');

                    // $booster_matched_bv=Member::where('total_matched_bv','>=',15000)->whereIn('id',$booster_ids)->sum('total_matched_bv');

                    // $booster_member_ids=Member::where('total_matched_bv','>=',15000)->whereIn('id',$booster_ids)->get()->pluck('member_id');

                    // $matched_bv=Member::where('total_matched_bv','>=',$matching_pv)->whereNotIn('id',$booster_member_ids)->sum('total_matched_bv');

                    $matched_bv=Member::where('total_matched_bv','>=',$matching_pv)->sum('total_matched_bv');
                    
                    $all_matched=$matched_bv;

                    $PayoutIncome->income_payout_parameter_1_name='matching_point_value';

                    if($payout->total_matched_bv==0){
                        $matching_point_value=0;
                    }else{
                        $matching_point_value=(($payout->sales_bv*$percent_of_total_company_bv)/100)/$all_matched;  
                    }

                    $PayoutIncome->income_payout_parameter_1_value=round($matching_point_value,4);
                    $PayoutIncome->save();
                }else{
                    $quilifier_matched_pv=0;
                    if($income->code=='TRIP_ALL' || $income->code=='VEHICLE_ALL' || $income->code=='HOUSE_ALL' || $income->code=='SUPER_GROWTH_ALL'){

                        // Get all qualifier whose rank is greator than or equals to gold and satisfies matching pv condtion

                        $quilifier_matched_pv=$MemberPayout::where('payout_id',$payout->id)
                        ->whereHas('member.rank',function($q){
                            $q->where('id','>=',[1]);
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
        $payout_tds=0;
        $payout_admin_fee=0;
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
            
            $member_payout_tds=0;
            $member_payout_admin_fee=0;

            foreach ($PayoutIncomes as $PayoutIncome) {
                // Count payout based on income.
                $income_tds=0;
                $income_admin_fee=0;

                if($PayoutIncome->income_payout_parameter_1_name=='matching_point_value'){
                    // MemberIncomePayout Calculation
                    $income_payout_amount=$MemberPayout->total_matched_bv*$PayoutIncome->income_payout_parameter_1_value;

                    if($PayoutIncome->income->code=='MATACHING'){
                        
                        $payout_amount=$MemberPayout->total_matched_bv*$PayoutIncome->income_payout_parameter_1_value;

                        $capping=$Member->rank->capping;
                        if($payout_amount>$capping){
                            $payout_amount=$capping;
                        }

                        $income_tds=($payout_amount*$tds_percentage)/100;
                        $income_admin_fee=($payout_amount*$admin_fee_percent)/100;
                        $payout_amount=$payout_amount-$income_tds;                        
                        $payout_amount=$payout_amount-$income_admin_fee;

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
                        $MemberPayoutIncome->tds=$income_tds;
                        $MemberPayoutIncome->admin_fee=$income_admin_fee;
                        $MemberPayoutIncome->save();
                    }else if($PayoutIncome->income->code=='CONSISTENCY'){
                       
                        if($income_payout_amount==0){
                            continue;
                        }

                        $ConsistencyIncomeParameter=IncomeParameter::where('income_id',$PayoutIncome->income_id)->where('name','matching_pv')->first();

                        // $booster_ids=Member::whereBetween('created_at',[$payout->sales_start_date,$payout->sales_end_date])->get()->pluck('id');

                        // $booster_member_ids=Member::where('total_matched_bv','>=',15000)->whereIn('id',$booster_ids)->get()->pluck('id')->toArray();


                        // $consistency_eligible=Member::where('total_matched_bv','>=',$ConsistencyIncomeParameter->value_1)->whereNotIn('id',$booster_member_ids)->get()->pluck('id')->toArray();

                         $consistency_eligible=Member::where('total_matched_bv','>=',$ConsistencyIncomeParameter->value_1)->pluck('id')->toArray();

                        $eligible_members=$consistency_eligible;

                        if(in_array($Member->id, $eligible_members)){
                            $payout_amount=$MemberPayout->total_matched_bv*$PayoutIncome->income_payout_parameter_1_value;

                            $rank=$Member->rank->name;
                            $IncomeParameter=IncomeParameter::where('income_id',$PayoutIncome->income_id)->where('name',$rank)->first();

                            if($payout_amount>$IncomeParameter->value_1){
                                $payout_amount=$IncomeParameter->value_1;
                            }

                            $income_tds=($payout_amount*$tds_percentage)/100;
                            $income_admin_fee=($payout_amount*$admin_fee_percent)/100;
                            $payout_amount=$payout_amount-$income_tds;                        
                            $payout_amount=$payout_amount-$income_admin_fee;

                            $TransactionType=TransactionType::where('name','Consistency Bonus')->first();
                            $MemberPayoutIncome=new MemberPayoutIncome;
                            $MemberPayoutIncome->payout_id=$payout->id;
                            $MemberPayoutIncome->income_id=$PayoutIncome->income_id;
                            $MemberPayoutIncome->member_id=$Member->id;
                            $MemberPayoutIncome->income_payout_parameter_1_name='matching_point_value';
                            $MemberPayoutIncome->income_payout_parameter_1_value=$PayoutIncome->income_payout_parameter_1_value;
                            $MemberPayoutIncome->payout_amount=$payout_amount;
                            $MemberPayoutIncome->tds=$income_tds;
                            $MemberPayoutIncome->admin_fee=$income_admin_fee;
                            $MemberPayoutIncome->save();
                        }                                               
                        
                    }else{

                        
                        $FundIncomeParameter=IncomeParameter::where('income_id',$PayoutIncome->income_id)->where('name','matching_pv')->first();
                        $matching_pv=$FundIncomeParameter->value_1;

                        if($PayoutIncome->income->code=='TRIP_ALL' || $PayoutIncome->income->code=='VEHICLE_ALL' || $PayoutIncome->income->code=='HOUSE_ALL' || $PayoutIncome->income->code=='SUPER_GROWTH_ALL'){

                            // Get all qualifier whose rank is greator than or equals to gold and satisfies matching pv condtion
                            
                            $income_payout_amount=0;

                            $is_qualify=MemberPayout::where('payout_id',$payout->id)
                                        ->where('member_id',$Member->id)
                                        ->whereHas('member.rank',function($q){
                                            $q->where('id','>=',1);
                                        })
                                        ->where('total_matched_bv','>=',$matching_pv)->first();

                            if($is_qualify){
                                $income_payout_amount=($is_qualify->total_matched_bv*$PayoutIncome->income_payout_parameter_1_value);
                                
                            }
                        }

                        if($PayoutIncome->income->code=='TRIP_DIA_EXE' || $PayoutIncome->income->code=='VEHICLE_DIA_EXE' || $PayoutIncome->income->code=='HOUSE_DIA_EXE' || $PayoutIncome->income->code=='SUPER_GROWTH_DIA_EXE'){

                            // Get all qualifier whose rank is greator than or equals to diamond and satisfies matching pv condtion

                            
                            $income_payout_amount=0;

                            $is_qualify=MemberPayout::where('payout_id',$payout->id)
                                        ->where('member_id',$Member->id)
                                        ->whereHas('member.rank',function($q){
                                            $q->where('id','>=',7);
                                        })
                                        ->where('total_matched_bv','>=',$matching_pv)->first();

                            if($is_qualify){
                                $income_payout_amount=($is_qualify->total_matched_bv*$PayoutIncome->income_payout_parameter_1_value);                                
                            }

                        }

                        if($PayoutIncome->income->code=='TRIP_DIPLOMAT' || $PayoutIncome->income->code=='VEHICLE_DIPLOMAT' || $PayoutIncome->income->code=='HOUSE_DIPLOMAT' || $PayoutIncome->income->code=='SUPER_GROWTH_DIPLOMAT'){

                            // Get all qualifier whose rank is greator than or equals to diplomat and satisfies matching pv condtion
                            
                            $income_payout_amount=0;

                            $is_qualify=MemberPayout::where('payout_id',$payout->id)
                                        ->where('member_id',$Member->id)
                                        ->whereHas('member.rank',function($q){
                                            $q->where('id','>=',8);
                                        })
                                        ->where('total_matched_bv','>=',$matching_pv)->first();

                            if($is_qualify){
                                $income_payout_amount=($is_qualify->total_matched_bv*$PayoutIncome->income_payout_parameter_1_value);                                
                            }
                        }

                        if($income_payout_amount==0){
                            continue;
                        }

                        

                        $income_tds=($income_payout_amount*$tds_percentage)/100;
                        $income_admin_fee=($income_payout_amount*$admin_fee_percent)/100;
                        $income_payout_amount=$income_payout_amount-$income_tds;                        
                        $income_payout_amount=$income_payout_amount-$income_admin_fee;

                        $TransactionType=TransactionType::where('name','Achievers’s Fund')->first();
                        $MemberPayoutIncome=new MemberPayoutIncome;
                        $MemberPayoutIncome->payout_id=$payout->id;
                        $MemberPayoutIncome->income_id=$PayoutIncome->income_id;
                        $MemberPayoutIncome->member_id=$Member->id;
                        $MemberPayoutIncome->income_payout_parameter_1_name='matching_point_value';
                        $MemberPayoutIncome->income_payout_parameter_1_value=$PayoutIncome->income_payout_parameter_1_value;
                        $MemberPayoutIncome->income_payout_parameter_2_name='matching_pv';
                        $MemberPayoutIncome->income_payout_parameter_2_value=$FundIncomeParameter->value_1;
                        $MemberPayoutIncome->payout_amount=$income_payout_amount;
                        $MemberPayoutIncome->tds=$income_tds;
                        $MemberPayoutIncome->admin_fee=$income_admin_fee;
                        $MemberPayoutIncome->save();
                    }                                        
                }else if($PayoutIncome->income_payout_parameter_1_name=='franchise_bonus_percent'){
                    if($PayoutIncome->income->code=='FRANCHISE'){

                        if($Member->rank_id <= 6){
                            $rank=Rank::find(6);
                        }else if($Member->rank_id == 7){
                            $rank=Rank::find(7);
                        }else if($Member->rank_id >= 8){
                            $rank=Rank::find(8);
                        }

                        $IncomeParameter=IncomeParameter::where('name',$rank->name)->where('income_id',$PayoutIncome->income_id)->first();


                        $TransactionType=TransactionType::where('name','Franchise Bonus')->first();
                        $MemberPayoutIncome=new MemberPayoutIncome;
                        $MemberPayoutIncome->payout_id=$payout->id;
                        $MemberPayoutIncome->income_id=$PayoutIncome->income_id;
                        $MemberPayoutIncome->member_id=$Member->id;
                        $MemberPayoutIncome->income_payout_parameter_1_name='franchise_bonus_percent';
                        $MemberPayoutIncome->income_payout_parameter_1_value=$IncomeParameter->value_1;                                                
                        $sponsored=$Member->sponsored->pluck('id')->toArray();
                        
                        $SponsorPayout=MemberPayout::whereIn('member_id',$sponsored)->where('payout_id',$payout->id)->sum('total_payout');
                        
                        $franchise_income=0;


                        if($SponsorPayout!=0){
                            
                            $franchise_income=($SponsorPayout*$IncomeParameter->value_1)/100;
                        }


                        if($franchise_income==0){
                            continue;
                        }

                        $income_tds=($franchise_income*$tds_percentage)/100;
                        $income_admin_fee=($franchise_income*$admin_fee_percent)/100;
                        $franchise_income=$franchise_income-$income_tds;
                        $franchise_income=$franchise_income-$income_admin_fee;

                        //$income_payout_amount=$franchise_income;
                        $MemberPayoutIncome->payout_amount=$franchise_income;
                        $MemberPayoutIncome->tds=$income_tds;
                        $MemberPayoutIncome->admin_fee=$income_admin_fee;
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

                            $Member->wallet_balance+=$MemberPayoutIncome->payout_amount;
                            $Member->save();
                        }
                    }
                    
                }
                // Getting Total Payout of all incomes for member.
                // $member_payout_tds+=$income_tds;
                // $member_payout_admin_fee+=$income_admin_fee;
                // $total_payout+=$income_payout_amount;
            }

            // $total_payout=$total_payout-$member_payout_tds;
            // $total_payout=$total_payout-$member_payout_admin_fee;

            $total_member_tds=MemberPayoutIncome::where('payout_id',$MemberPayout->payout_id)->where('member_id',$Member->id)->sum('tds');

            $total_member_admin_fee=MemberPayoutIncome::where('payout_id',$MemberPayout->payout_id)->where('member_id',$Member->id)->sum('admin_fee');

            $total_member_payout_amount=MemberPayoutIncome::where('payout_id',$MemberPayout->payout_id)->where('member_id',$Member->id)->sum('payout_amount');

            $MemberPayout->total_payout=$total_member_payout_amount;
            $MemberPayout->tds=$total_member_tds;
            $MemberPayout->admin_fee=$total_member_admin_fee;
            $MemberPayout->save();
            $Member->current_personal_pv=0;
            $Member->wallet_balance+=$total_payout;
            $Member->save();
            // calculating total payout for this payout run
            $all_income_payout_total+=$total_payout;
            $payout_tds+=$member_payout_tds;
            $payout_admin_fee+=$member_payout_admin_fee;
        }

        // Calculating income wise total payout.
        foreach ($PayoutIncomes as $PayoutIncome) {
            $TotalIncomePayout= MemberPayoutIncome::where('payout_id',$PayoutIncome->payout_id)->where('income_id',$PayoutIncome->income_id)->sum('payout_amount');
            $PayoutIncome->payout_amount=$TotalIncomePayout;
            $PayoutIncome->save();
        }

        $total_tds=MemberPayoutIncome::where('payout_id',$MemberPayout->payout_id)->sum('tds');

        $total_admin_fee=MemberPayoutIncome::where('payout_id',$MemberPayout->payout_id)->sum('admin_fee');

        $total_payout_amount=MemberPayoutIncome::where('payout_id',$MemberPayout->payout_id)->sum('payout_amount');

        // Saving total payout to payout
        // $all_income_payout_total=$all_income_payout_total-$payout_tds-$payout_admin_fee;
        $payout->total_payout=$total_payout_amount;
        $payout->tds=$total_tds;
        $payout->admin_fee=$total_admin_fee;
        $payout->save();

    }

    public function updateRank($payout){
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
