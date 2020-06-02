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
use App\Models\Admin\Payout;
use App\Models\Admin\PayoutIncome;
use App\Models\Admin\MemberPayout;
use App\Models\Admin\MemberPayoutIncome;
use App\Models\Admin\WalletTransaction;
use App\Models\Superadmin\TransactionType;

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
            $percent_of_total_company_bv=0;

            foreach ($income->income_parameters as $parameter) {
                // Get parameter for matching bonus income.
                if($parameter->name=='percent_of_total_company_bv'){
                    $percent_of_total_company_bv=$parameter->value_1;
                }
            }

            // Counting matching point value based on parameters and plan criteria
            $PayoutIncome->income_payout_parameter_1_name='matching_point_value';
            $matching_point_value=(($payout->sales_bv*$percent_of_total_company_bv)/100)/$payout->total_matched_bv;
            $PayoutIncome->income_payout_parameter_1_value=round($matching_point_value,4);
            $PayoutIncome->save();
        }

        $all_income_payout_total=0;
        // Calculating Member Payout Amount
        foreach ($Members as $Member) {
            // Get Incomes included in payout.
            $total_payout=0;
            $PayoutIncomes=PayoutIncome::where('payout_id',$payout->id)->get();
            $MemberPayout= MemberPayout::where('member_id',$Member->id)->where('payout_id',$payout->id)->first();
            foreach ($PayoutIncomes as $PayoutIncome) {
                // Count payout based on income.
                if($PayoutIncome->income_payout_parameter_1_name=='matching_point_value'){
                    // MemberIncomePayout Calculation
                    $MemberPayoutIncome=new MemberPayoutIncome;
                    $MemberPayoutIncome->payout_id=$payout->id;
                    $MemberPayoutIncome->income_id=$PayoutIncome->income_id;
                    $MemberPayoutIncome->member_id=$Member->id;
                    $MemberPayoutIncome->income_payout_parameter_1_name='matching_point_value';
                    $MemberPayoutIncome->income_payout_parameter_1_value=$PayoutIncome->income_payout_parameter_1_value;
                    $MemberPayoutIncome->payout_amount=($MemberPayout->total_matched_bv*$PayoutIncome->income_payout_parameter_1_value);
                    $MemberPayoutIncome->save();   
                    $TransactionType=TransactionType::where('name','Matching Bonus')->first();

                    if($TransactionType){
                        $WalletTransaction=new WalletTransaction;
                        $WalletTransaction->member_id=$Member->id;
                        $WalletTransaction->amount=$MemberPayoutIncome->payout_amount;
                        $WalletTransaction->balance=$MemberPayoutIncome->payout_amount+$Member->wallet_balance;
                        $WalletTransaction->transaction_type_id=$TransactionType->id;
                        $WalletTransaction->transfered_to=$Member->user->id;
                        $WalletTransaction->note='Payout Income';
                        $WalletTransaction->save();
                    }
                    // Getting Total Payout of all incomes for member.                
                    $total_payout+=$MemberPayoutIncome->payout_amount;
                }
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
}
