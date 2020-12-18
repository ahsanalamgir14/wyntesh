<?php

namespace App\Listeners;

use App\Events\GeneratePayoutEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Admin\Income;
use App\Models\Admin\IncomeParameter;
use App\Models\Admin\Sale;
use App\Models\User\Order;
use App\Models\Admin\Member;
use App\Models\Admin\MembersLegPv;
use App\Models\Admin\Rank;
use App\Models\Admin\RankLog;
use App\Models\Admin\CompanySetting;
use App\Models\Admin\Payout;
use App\Models\Admin\PayoutIncome;
use App\Models\Admin\MemberPayout;
use App\Models\Admin\MemberPayoutIncome;
use App\Models\Admin\AffiliateBonus;
use App\Models\Admin\MemberIncomeHolding;
use App\Models\Admin\IncomeWalletTransactions;
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

    public $tds_percentage;
    public $admin_fee_percent;

    public function __construct()
    {
        $this->tds_percentage=CompanySetting::getValue('tds_percentage');
        $this->admin_fee_percent=0;

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
        $sales_amount=Sale::whereDate('created_at','<=', $payout->sales_end_date)
                            ->whereDate('created_at','>=', $payout->sales_start_date)
                            ->sum('final_amount_company');

        $total_bv=Sale::whereDate('created_at','<=',$payout->sales_end_date)
                        ->whereDate('created_at','>=',$payout->sales_start_date)
                        ->sum('pv');

        $total_sales_gst=Sale::whereDate('created_at','<=',$payout->sales_end_date)
                        ->whereDate('created_at','>=',$payout->sales_start_date)
                        ->sum('gst');

        $total_sales_shipping_fee=Sale::whereDate('created_at','<=',$payout->sales_end_date)
                        ->whereDate('created_at','>=',$payout->sales_start_date)
                        ->sum('shipping_fee');

        $total_sales_admin_fee=Sale::whereDate('created_at','<=',$payout->sales_end_date)
                        ->whereDate('created_at','>=',$payout->sales_start_date)
                        ->sum('admin_fee');

        $total_payout_sales_amount=Sale::whereDate('created_at','<=',$payout->sales_end_date)
                        ->whereDate('created_at','>=',$payout->sales_start_date)
                        ->sum('amount');


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
            $MemberPayout->created_at=$payout->sales_end_date;
            $MemberPayout->save();

            $matched_bv=0;
            $carry_forward=0;
            $carry_forward_position=0;
            $leg_1_pv=0;
            $leg_2_pv=0;

            //Counting Carry forward and Matched points of Member Legs.

            //Getting Member Legs in decenting based on current PV
            $legs=0;
            $legs= MembersLegPv::addSelect(['*', \DB::raw('sum(pv) as totalPv')])
                        ->whereDate('created_at','<=',$payout->sales_end_date)
                        ->whereDate('created_at','>=',$payout->sales_start_date)
                        ->where('member_id',$Member->id)
                        ->orderBy('totalPv','desc')
                        ->groupBy('position')
                        ->get()->pluck('totalPv','position')->toArray();

            $last_carry_forward=MemberCarryForwardPv::where('member_id',$Member->id)->orderBy('payout_id','desc')->first();

            if($last_carry_forward){
                if(count($legs)){
                    $exsting_pv=intval(isset($legs[$last_carry_forward->position])?$legs[$last_carry_forward->position]:0);
                    $legs[$last_carry_forward->position]=$exsting_pv+$last_carry_forward->pv;
                }
            }

            arsort($legs);

            $index = 0;
            foreach ($legs as $position => $pv) {
                if($index==0){
                    $leg_1_pv=$pv;
                    $carry_forward_position=$position;

                    // If only 1 leg then no matching bonus, carry forward all current pv
                    if(count($legs)==1){
                        $carry_forward=$pv;                        
                    }
                    $index++;
                    continue;
                }

                if($index==1){
                    $leg_2_pv=$pv;
                    // Count carry forward
                    $carry_forward=$leg_1_pv-$leg_2_pv;
                }
                       
                // Add current pv to matched_bv of all legs except strong one.
                $matched_bv+=$pv;
                $index++;
            }

            if($matched_bv<=0){
                $matched_bv=0;
            }

            // dd($carry_forward);
            if(count($legs)!== 0){
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
            $Member->total_matched_bv+=$matched_bv;
            $Member->save();
            
            $MemberPayout->total_matched_bv=$matched_bv;
            $MemberPayout->total_carry_forward_bv=$carry_forward;
            $MemberPayout->save();

        }

        // Save total values to payouts.
        $payout->sales_bv=$total_bv;
        $payout->sales_amount=$sales_amount;
        $payout->sales_gst=$total_sales_gst;
        $payout->sales_shipping_fee=$total_sales_shipping_fee;
        $payout->sales_admin_fee=$total_sales_admin_fee;
        $payout->sales_total_amount=$total_payout_sales_amount;
        $payout->total_matched_bv=$total_mached_bv;
        $payout->total_carry_forward_bv=$total_carry_forward_bv;
        $payout->save();

        // Get Income Ids of Payout 
        $Incomes=Income::whereIn('id',$income_ids)->get();
        
        foreach ($Incomes as $income) {
            // Get Payout income from payout id
            $PayoutIncome=PayoutIncome::where('payout_id',$payout->id)->where('income_id',$income->id)->first();
            if($income->code=='SQUAD'){
                $this->PayoutSquadIncomeFactor($income,$PayoutIncome,$payout);
            }
        }

        $Members=Member::whereHas('user',function($q){
            $q->where('is_active',1);
        })->orderBy('level','desc')->get();

        foreach ($Members as $Member) {
            $memberPayout = MemberPayout::where('member_id',$Member->id)->where('payout_id',$payout->id)->first();
            $Incomes = Income::whereIn('id',$income_ids)->get();
            foreach($Incomes as $income){
                if($income->code=='SQUAD'){
                    $this->PayoutSquadIncome($income,$payout,$memberPayout,$Member);
                }

            }            
        }

        $Incomes=Income::whereIn('id',$income_ids)->get();
        foreach ($Incomes as $income) {
            // Get Payout income from payout id
            $PayoutIncome=PayoutIncome::where('payout_id',$payout->id)->where('income_id',$income->id)->first();
            
            if($income->code=='ELEVATION'){
                $this->PayoutElevationIncomeFactor($income,$PayoutIncome,$payout);
            }

            if($income->code=='LUXURY'){
                $this->PayoutLuxuryIncomeFactor($income,$PayoutIncome,$payout);
            }

            if($income->code=='PREMIUM'){
                $this->PayoutPremiumIncomeFactor($income,$PayoutIncome,$payout);
            }
        }

        $Members=Member::whereHas('user',function($q){
            $q->where('is_active',1);
        })->orderBy('level','desc')->get();

        foreach ($Members as $Member) {
            $memberPayout = MemberPayout::where('member_id',$Member->id)->where('payout_id',$payout->id)->first();
            $Incomes = Income::whereIn('id',$income_ids)->get();
            foreach($Incomes as $income){
               
                if($income->code=='ELEVATION'){
                    $this->PayoutElevationIncome($income,$payout,$memberPayout,$Member);
                }

                if($income->code=='LUXURY'){
                    $this->PayoutLuxuryIncome($income,$payout,$memberPayout,$Member);
                }

                if($income->code=='PREMIUM'){
                    $this->PayoutPremiumIncome($income,$payout,$memberPayout,$Member);
                }
            }

            $MemberPayout=MemberPayout::where('payout_id',$payout->id)->where('member_id',$Member->id)->first();
            $total_member_payout_amount=MemberPayoutIncome::where('payout_id',$payout->id)->where('member_id',$Member->id)->sum('payout_amount');
            $total_member_tds=MemberPayoutIncome::where('payout_id',$payout->id)->where('member_id',$Member->id)->sum('tds');
            $total_member_admin_fee=MemberPayoutIncome::where('payout_id',$payout->id)->where('member_id',$Member->id)->sum('admin_fee');
            
            $MemberPayout->total_payout=$total_member_payout_amount;
            $MemberPayout->tds=$total_member_tds;
            $MemberPayout->admin_fee=$total_member_admin_fee;
            $MemberPayout->save();
            $Member->current_personal_pv=0;
            $Member->save();
        }

        $PayoutIncomes=PayoutIncome::where('payout_id',$payout->id)->get();
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
        $payout->ended_at=Carbon::now();
        $payout->save();

        $this->updateRank($payout);
    }

    public function PayoutElevationIncomeFactor($income,$PayoutIncome,$payout) {
        $monthly_company_turnover_percent=0;
        
        foreach ($income->income_parameters as $parameter) {
            if($parameter->name=='monthly_company_turnover_percent'){
                $monthly_company_turnover_percent=$parameter->value_1;
            }
        }

        // Counting matching point value based on parameters and plan criteria
        $PayoutIncome->income_payout_parameter_1_name='ebp';
        
        $eb_eligibles=$this->getElevationEligibles($income,$payout);
        $payout_month=Carbon::createFromFormat('Y-m-d', $payout->sales_start_date)->format('m');
        $month_payouts=Payout::whereMonth('sales_start_date',$payout_month)->get()->pluck('id')->toArray();
        $ebp=MemberPayout::whereIn('member_id',$eb_eligibles)->whereIn('payout_id',$month_payouts)->sum('total_matched_bv');

        $current_month_bv=Sale::whereMonth('created_at',$payout_month)->sum('pv');

        if($ebp==0){
            $income_factor=0;
        }else{            
            $income_factor=(($current_month_bv*$monthly_company_turnover_percent)/100)/$ebp;
        }

        $PayoutIncome->income_payout_parameter_1_value=round($income_factor,4);
        $PayoutIncome->save();
    }
    
    public function getElevationEligibles($income,$payout){

        $rank_4_criteria=IncomeParameter::where('income_id',$income->id)->where('name','rank')->where('value_1',4)->first();
        $rank_5_criteria=IncomeParameter::where('income_id',$income->id)->where('name','rank')->where('value_1',5)->first();
        $rank_6_criteria=IncomeParameter::where('income_id',$income->id)->where('name','rank')->where('value_1',6)->first();
        $rank_7_criteria=IncomeParameter::where('income_id',$income->id)->where('name','rank')->where('value_1',7)->first();
        $rank_8_criteria=IncomeParameter::where('income_id',$income->id)->where('name','rank')->where('value_1',8)->first();
        
        $date = Carbon::createFromFormat('Y-m-d', $payout->sales_start_date);
        $start=$date->startOfMonth()->format('Y-m-d');
        $end=$date->endOfMonth()->format('Y-m-d');

        $eligible_4=$this->getSquadAffiliateEligible($start,$end,$rank_4_criteria->value_2,$rank_4_criteria->value_1);
        $eligible_5=$this->getSquadAffiliateEligible($start,$end,$rank_5_criteria->value_2,$rank_5_criteria->value_1);
        $eligible_6=$this->getSquadAffiliateEligible($start,$end,$rank_6_criteria->value_2,$rank_6_criteria->value_1);
        $eligible_7=$this->getSquadAffiliateEligible($start,$end,$rank_7_criteria->value_2,$rank_7_criteria->value_1);

        $eligible_8=Member::where('rank_id',8)->whereHas('user',function($q){
                                                $q->where('is_active',1);
                                            })->get()->pluck('id')->toArray();

        $all_eligibles=array_merge($eligible_4,$eligible_5,$eligible_6,$eligible_7,$eligible_8);

        return $all_eligibles;
    }

    public function PayoutElevationIncome($income,$payout,$memberPayout,$Member) {

        $totalIncomeValue = 0;
        $payoutIcome = PayoutIncome::where('payout_id',$payout->id)->where('income_id',$income->id)->first();
        $factor = $payoutIcome->income_payout_parameter_1_value;
        
        

        $eb_eligibles=$this->getElevationEligibles($income,$payout);

        if(!in_array($Member->id, $eb_eligibles)){
            return 0;
        }

        $payout_month=Carbon::createFromFormat('Y-m-d', $payout->sales_start_date)->format('m');
        $month_payouts=Payout::whereMonth('sales_start_date',$payout_month)->get()->pluck('id')->toArray();
        $member_matched=MemberPayout::where('member_id',$Member->id)->whereIn('payout_id',$month_payouts)->sum('total_matched_bv');

        $totalIncomeValue = $member_matched*$factor;

        if($totalIncomeValue!=0){

            $income_tds=($totalIncomeValue*$this->tds_percentage)/100;
            $income_admin_fee=($totalIncomeValue*$this->admin_fee_percent)/100;
            $totalIncomeValue=$totalIncomeValue-$income_tds;                        
            $totalIncomeValue=$totalIncomeValue-$income_admin_fee;

            $Member->income_wallet_balance += $totalIncomeValue;
            $Member->save();

            $IncomeWalletTransactions=new IncomeWalletTransactions;
            $IncomeWalletTransactions->member_id            = $Member->id;
            $IncomeWalletTransactions->balance              = $Member->income_wallet_balance;
            $IncomeWalletTransactions->amount               = $totalIncomeValue;
            $IncomeWalletTransactions->transfered_to       = $Member->user->id;
            $IncomeWalletTransactions->note                 = 'Elevation Bonus';
            $this->commonWalletTransectionEntry($IncomeWalletTransactions,'Elevation Bonus');

            $MemberPayoutIncome = new MemberPayoutIncome;
            $MemberPayoutIncome->member_id                              = $Member->id;
            $MemberPayoutIncome->payout_id                              = $payout->id;
            $MemberPayoutIncome->income_id                              = $income->id;
            $MemberPayoutIncome->payout_amount                          = $totalIncomeValue;
            $MemberPayoutIncome->income_payout_parameter_1_name         = $income->income_payout_parameter_1_name;
            $MemberPayoutIncome->income_payout_parameter_1_value        = $income->income_payout_parameter_1_value;
            $MemberPayoutIncome->tds=$income_tds;
            $MemberPayoutIncome->admin_fee=$income_admin_fee;
            $MemberPayoutIncome->created_at=$payout->sales_end_date;
            $MemberPayoutIncome->save();
        }
    }

    public function PayoutLuxuryIncomeFactor($income,$PayoutIncome,$payout) {
        $monthly_company_turnover_percent=0;
        $accumulating_rank=2;

        foreach ($income->income_parameters as $parameter) {
            if($parameter->name=='monthly_company_turnover_percent'){
                $monthly_company_turnover_percent=$parameter->value_1;
            }
            if($parameter->name=='accumulating_rank'){
                $accumulating_rank=$parameter->value_1;
            }
        }

        // Counting matching point value based on parameters and plan criteria
        $PayoutIncome->income_payout_parameter_1_name='lbp';
        $lbp_eligibles=Member::where('rank_id','>=',$accumulating_rank)->whereHas('user',function($q){
                                                $q->where('is_active',1);
                                            })->get()->pluck('id')->toArray();
        $payout_month=Carbon::createFromFormat('Y-m-d', $payout->sales_start_date)->format('m');
        $month_payouts=Payout::whereMonth('sales_start_date',$payout_month)->get()->pluck('id')->toArray();

        $lbp=MemberPayout::whereIn('member_id',$lbp_eligibles)->whereIn('payout_id',$month_payouts)->sum('total_matched_bv');
        $current_month_bv=Sale::whereMonth('created_at',$payout_month)->sum('pv');

        if($lbp==0){
            $income_factor=0;
        }else{            
            $income_factor=(($current_month_bv*$monthly_company_turnover_percent)/100)/$lbp;
        }

        $PayoutIncome->income_payout_parameter_1_value=round($income_factor,4);
        $PayoutIncome->save();
    }

    public function PayoutLuxuryIncome($income,$payout,$memberPayout,$Member) {

        $totalIncomeValue = 0;
        $payoutIcome = PayoutIncome::where('payout_id',$payout->id)->where('income_id',$income->id)->first();
        $factor = $payoutIcome->income_payout_parameter_1_value;

        $accumulating_rank=2;
        $disbursal_rank=8;

        foreach ($income->income_parameters as $parameter) {            
            if($parameter->name=='accumulating_rank'){
                $accumulating_rank=$parameter->value_1;
            }
            if($parameter->name=='disbursal_rank'){
                $disbursal_rank=$parameter->value_1;
            }
        }

        $lbp_eligibles=Member::where('rank_id','>=',$accumulating_rank)
                                ->whereHas('user',function($q){
                                    $q->where('is_active',1);
                                })
                                ->get()
                                ->pluck('id')
                                ->toArray();

        
        if(!in_array($Member->id, $lbp_eligibles)){
            return 0;
        }

        $payout_month=Carbon::createFromFormat('Y-m-d', $payout->sales_start_date)->format('m');
        $month_payouts=Payout::whereMonth('sales_start_date',$payout_month)->get()->pluck('id')->toArray();
        $member_matched=MemberPayout::where('member_id',$Member->id)->whereIn('payout_id',$month_payouts)->sum('total_matched_bv');

        $totalIncomeValue = $member_matched*$factor;


        if($totalIncomeValue!=0){

            $income_tds=($totalIncomeValue*$this->tds_percentage)/100;
            $income_admin_fee=($totalIncomeValue*$this->admin_fee_percent)/100;
            $totalIncomeValue=$totalIncomeValue-$income_tds;                        
            $totalIncomeValue=$totalIncomeValue-$income_admin_fee;


            if($Member->rank_id==$disbursal_rank){

                $Member->income_wallet_balance += $totalIncomeValue;
                $Member->save();
                
                $IncomeWalletTransactions=new IncomeWalletTransactions;
                $IncomeWalletTransactions->member_id            = $Member->id;
                $IncomeWalletTransactions->balance              = $Member->income_wallet_balance;
                $IncomeWalletTransactions->amount               = $totalIncomeValue;
                $IncomeWalletTransactions->transfered_to       = $Member->user->id;
                $IncomeWalletTransactions->note                 = 'Luxury Bonus';
                $this->commonWalletTransectionEntry($IncomeWalletTransactions,'Luxury Bonus');
            }else{
                $MemberIncomeHolding=new MemberIncomeHolding;
                $MemberIncomeHolding->member_id=$Member->id;
                $MemberIncomeHolding->payout_id=$payout->id;
                $MemberIncomeHolding->income_id=$income->id;
                $MemberIncomeHolding->rank_id=$Member->rank_id;
                $MemberIncomeHolding->amount=$totalIncomeValue;
                $MemberIncomeHolding->is_paid=0;
                $MemberIncomeHolding->save();

            }

            $MemberPayoutIncome = new MemberPayoutIncome;
            $MemberPayoutIncome->member_id                              = $Member->id;
            $MemberPayoutIncome->payout_id                              = $payout->id;
            $MemberPayoutIncome->income_id                              = $income->id;
            $MemberPayoutIncome->payout_amount                          = $totalIncomeValue;
            $MemberPayoutIncome->income_payout_parameter_1_name         = $income->income_payout_parameter_1_name;
            $MemberPayoutIncome->income_payout_parameter_1_value        = $income->income_payout_parameter_1_value;
            $MemberPayoutIncome->tds=$income_tds;
            $MemberPayoutIncome->admin_fee=$income_admin_fee;
            $MemberPayoutIncome->created_at=$payout->sales_end_date;
            $MemberPayoutIncome->save();
        }
    }

    public function PayoutPremiumIncomeFactor($income,$PayoutIncome,$payout) {
        $monthly_company_turnover_percent=0;
        
        foreach ($income->income_parameters as $parameter) {
            if($parameter->name=='monthly_company_turnover_percent'){
                $monthly_company_turnover_percent=$parameter->value_1;
            }
        }

        // Counting matching point value based on parameters and plan criteria
        $PayoutIncome->income_payout_parameter_1_name='pbp';
        $pb_eligibles_and_points=$this->getPremiumEligibles($income,$payout);
        $total_points_collected=array_sum(array_values($pb_eligibles_and_points));
        $pb_eligibles=array_keys($pb_eligibles_and_points);   

        $payout_month=Carbon::createFromFormat('Y-m-d', $payout->sales_start_date)->format('m');      
        $current_month_bv=Sale::whereMonth('created_at',$payout_month)->sum('pv');     

        if($total_points_collected==0){
            $income_factor=0;
        }else{            
            $income_factor=(($current_month_bv*$monthly_company_turnover_percent)/100)/$total_points_collected;    
        }
        
        $PayoutIncome->income_payout_parameter_1_value=round($income_factor,4);
        $PayoutIncome->save();
    }

    public function getPremiumEligibles($income,$payout){

        $rank_4_criteria=IncomeParameter::where('income_id',$income->id)->where('name','rank')->where('value_1',4)->first();
        $rank_5_criteria=IncomeParameter::where('income_id',$income->id)->where('name','rank')->where('value_1',5)->first();
        $rank_6_criteria=IncomeParameter::where('income_id',$income->id)->where('name','rank')->where('value_1',6)->first();
        $rank_7_criteria=IncomeParameter::where('income_id',$income->id)->where('name','rank')->where('value_1',7)->first();
        $rank_8_criteria=IncomeParameter::where('income_id',$income->id)->where('name','rank')->where('value_1',8)->first();

        $date = Carbon::createFromFormat('Y-m-d', $payout->sales_start_date);
        $start=$date->startOfMonth()->format('Y-m-d');
        $end=$date->endOfMonth()->format('Y-m-d');

        $eligible_4=$this->getSquadAffiliateEligible($start,$end,$rank_4_criteria->value_2,$rank_4_criteria->value_1);
        $eligible_5=$this->getSquadAffiliateEligible($start,$end,$rank_5_criteria->value_2,$rank_5_criteria->value_1);
        $eligible_6=$this->getSquadAffiliateEligible($start,$end,$rank_6_criteria->value_2,$rank_6_criteria->value_1);
        $eligible_7=$this->getSquadAffiliateEligible($start,$end,$rank_7_criteria->value_2,$rank_7_criteria->value_1);
       
        $eligible_8=Member::where('rank_id',8)->whereHas('user',function($q){
                                                $q->where('is_active',1);
                                            })->get()->pluck('id')->toArray();

        $PremiumEls=array_merge($eligible_4,$eligible_5,$eligible_6,$eligible_7);

        $totalPP=0;


        foreach ($PremiumEls as $key=>$pmid) {
            $PMem=Member::find($pmid);
            $spaf=$this->calulatePremiumPoints($PMem,$start,$end);
            if($PMem->rank_id==4){
                $point=0;
                $point=intdiv($spaf,$rank_4_criteria->value_2);
                for ($i=0; $i <$point-1 ; $i++) { 
                    array_push($eligible_4, $pmid);
                }
            }
            if($PMem->rank_id==5){
                $point=0;
                $point=intdiv($spaf,$rank_5_criteria->value_2);
                for ($i=0; $i <$point-1 ; $i++) { 
                    array_push($eligible_5, $pmid);
                }
            }
            if($PMem->rank_id==6){
                $point=0;
                $point=intdiv($spaf,$rank_6_criteria->value_2);
                for ($i=0; $i <$point-1 ; $i++) { 
                    array_push($eligible_6, $pmid);
                }
            }
            if($PMem->rank_id==7){
                $point=0;
                $point=intdiv($spaf,$rank_7_criteria->value_2);
                for ($i=0; $i <$point-1 ; $i++) { 
                    array_push($eligible_7, $pmid);
                }
            }
        }

        $all_eligibles=array_merge($eligible_4,$eligible_5,$eligible_6,$eligible_7,$eligible_8,$eligible_8,$eligible_8);

        $all_eligible_and_points=array_count_values($all_eligibles);

        return $all_eligible_and_points;
    }

    public function PayoutPremiumIncome($income,$payout,$memberPayout,$Member) {

        $totalIncomeValue = 0;
        $payoutIcome = PayoutIncome::where('payout_id',$payout->id)->where('income_id',$income->id)->first();
        $factor = $payoutIcome->income_payout_parameter_1_value;

        $pb_eligibles_and_points=$this->getPremiumEligibles($income,$payout);

        $pb_eligibles=array_keys($pb_eligibles_and_points);

        if(!in_array($Member->id, $pb_eligibles)){
            return 0;
        }

        $totalIncomeValue = $pb_eligibles_and_points[$Member->id]*$factor;

        if($totalIncomeValue!=0){

            $income_tds=($totalIncomeValue*$this->tds_percentage)/100;
            $income_admin_fee=($totalIncomeValue*$this->admin_fee_percent)/100;
            $totalIncomeValue=$totalIncomeValue-$income_tds;                        
            $totalIncomeValue=$totalIncomeValue-$income_admin_fee;

            $Member->income_wallet_balance += $totalIncomeValue;
            $Member->save();

            $IncomeWalletTransactions=new IncomeWalletTransactions;
            $IncomeWalletTransactions->member_id            = $Member->id;
            $IncomeWalletTransactions->balance              = $Member->income_wallet_balance;
            $IncomeWalletTransactions->amount               = $totalIncomeValue;
            $IncomeWalletTransactions->transfered_to       = $Member->user->id;
            $IncomeWalletTransactions->note                 = 'Premium Bonus';
            $this->commonWalletTransectionEntry($IncomeWalletTransactions,'Premium Bonus');

            $MemberPayoutIncome = new MemberPayoutIncome;
            $MemberPayoutIncome->member_id                              = $Member->id;
            $MemberPayoutIncome->payout_id                              = $payout->id;
            $MemberPayoutIncome->income_id                              = $income->id;
            $MemberPayoutIncome->payout_amount                          = $totalIncomeValue;
            $MemberPayoutIncome->income_payout_parameter_1_name         = $income->income_payout_parameter_1_name;
            $MemberPayoutIncome->income_payout_parameter_1_value        = $income->income_payout_parameter_1_value;
            $MemberPayoutIncome->tds=$income_tds;
            $MemberPayoutIncome->admin_fee=$income_admin_fee;
            $MemberPayoutIncome->created_at=$payout->sales_end_date;
            $MemberPayoutIncome->save();
        }
    }

    

    public function PayoutSquadIncomeFactor($income,$PayoutIncome,$payout) {
        $monthly_company_turnover_percent=0;
        foreach ($income->income_parameters as $parameter) {
            if($parameter->name=='monthly_company_turnover_percent'){
                $monthly_company_turnover_percent=$parameter->value_1;
            }
        }
        // Counting matching point value based on parameters and plan criteria
        $PayoutIncome->income_payout_parameter_1_name='sbp';
        if($payout->total_matched_bv==0){
            $income_factor=0;
        }else{
            $income_factor=(($payout->sales_bv*$monthly_company_turnover_percent)/100)/$payout->total_matched_bv;    
        }
        
        $PayoutIncome->income_payout_parameter_1_value=round($income_factor,4);
        $PayoutIncome->save();
    }

    public function PayoutSquadIncome($income,$payout,$memberPayout,$Member) {

        $totalIncomeValue = 0;
        $payoutIcome = PayoutIncome::where('payout_id',$payout->id)->where('income_id',$income->id)->first();
        $factor = $payoutIcome->income_payout_parameter_1_value;
        $totalIncomeValue = $memberPayout->total_matched_bv*$factor;

        if($totalIncomeValue!=0){

            $income_tds=($totalIncomeValue*$this->tds_percentage)/100;
            $income_admin_fee=($totalIncomeValue*$this->admin_fee_percent)/100;
            $totalIncomeValue=$totalIncomeValue-$income_tds;                        
            $totalIncomeValue=$totalIncomeValue-$income_admin_fee;

            $Member->income_wallet_balance += $totalIncomeValue;
            $Member->save();

            $IncomeWalletTransactions=new IncomeWalletTransactions;
            $IncomeWalletTransactions->member_id            = $Member->id;
            $IncomeWalletTransactions->balance              = $Member->income_wallet_balance;
            $IncomeWalletTransactions->amount               = $totalIncomeValue;
            $IncomeWalletTransactions->transfered_to       = $Member->user->id;
            $IncomeWalletTransactions->note                 = 'Squad Bonus';
            $this->commonWalletTransectionEntry($IncomeWalletTransactions,'Squad Bonus');

            $MemberPayoutIncome = new MemberPayoutIncome;
            $MemberPayoutIncome->member_id                              = $Member->id;
            $MemberPayoutIncome->payout_id                              = $payout->id;
            $MemberPayoutIncome->income_id                              = $income->id;
            $MemberPayoutIncome->payout_amount                          = $totalIncomeValue;
            $MemberPayoutIncome->income_payout_parameter_1_name         = $income->income_payout_parameter_1_name;
            $MemberPayoutIncome->income_payout_parameter_1_value        = $income->income_payout_parameter_1_value;
            $MemberPayoutIncome->tds=$income_tds;
            $MemberPayoutIncome->admin_fee=$income_admin_fee;
            $MemberPayoutIncome->created_at=$payout->sales_end_date;
            $MemberPayoutIncome->save();
        }
    }

    public function commonWalletTransectionEntry($IncomeWalletTransactions,$transectointype) {
        $TransactionType=TransactionType::where('name',$transectointype)->first();
        $IncomeWalletTransactions->transaction_type_id  = $TransactionType->id;
        $IncomeWalletTransactions->save();
    }

    public function getSquadPlusAffiliate($Member){
        
         $results = DB::select(DB::raw("SELECT sum(amt) total_amt from (SELECT ab.member_id,sum(amount) as amt FROM `affiliate_bonus` as ab right join `members` as m on m.id = ab.member_id  group by ab.member_id UNION SELECT tp.member_id,sum(payout_amount+tds) as amt FROM `member_payout_incomes` as tp left join `members` as m on m.id = tp.member_id where income_id=3 group by member_id) tmp where tmp.member_id=".$Member->id." group by tmp.member_id ") );

        if($results){            
            return floatval($results[0]->total_amt);
        }else{
            return 0;
        }
    }

    public function getSquadPlusAffiliatePinnacle($Member){
        
         $results = DB::select(DB::raw("SELECT sum(amt) total_amt from (SELECT ab.member_id,sum(amount) as amt FROM `affiliate_bonus` as ab right join `members` as m on m.id = ab.member_id  group by ab.member_id UNION SELECT tp.member_id,sum(payout_amount+tds) as amt FROM `member_payout_incomes` as tp left join `members` as m on m.id = tp.member_id  group by member_id) tmp where tmp.member_id=".$Member->id." group by tmp.member_id ") );

        if($results){
            return floatval($results[0]->total_amt);
        }else{
            return 0;
        }
    }

    public function getSquadAffiliateEligible($from,$to,$criteria,$rank){
                
        $results = DB::select(DB::raw("SELECT  member_id from (SELECT ab.member_id,m.rank_id,sum(amount) as amt FROM `affiliate_bonus` as ab right join `members` as m on m.id = ab.member_id where date(`ab`.`created_at`) >= '".$from."' and date(`ab`.`created_at`) <= '".$to."' group by ab.member_id UNION SELECT tp.member_id,m.rank_id,sum(payout_amount+tds) as amt FROM `member_payout_incomes` as tp left join `members` as m on m.id = tp.member_id where income_id=3 and date(`tp`.`created_at`) >= '".$from."' and date(`tp`.`created_at`) <= '".$to."' group by member_id) tmp where amt >= '".$criteria."' and rank_id ='".$rank."'  group by tmp.member_id") );

        $eligibles=array_column($results,'member_id');
        return $eligibles;
    }

    public function calulatePremiumPoints($Member,$from,$to){
        $results=DB::select(DB::raw("SELECT sum(amt) total_amt from (SELECT ab.member_id,sum(amount) as amt FROM `affiliate_bonus` as ab right join `members` as m on m.id = ab.member_id where date(`ab`.`created_at`) >= '".$from."' and date(`ab`.`created_at`) <= '".$to."' group by ab.member_id UNION SELECT tp.member_id,sum(payout_amount+tds) as amt FROM `member_payout_incomes` as tp left join `members` as m on m.id = tp.member_id where income_id=3 and date(`tp`.`created_at`) >= '".$from."' and date(`tp`.`created_at`) <= '".$to."' group by member_id) tmp where tmp.member_id=".$Member->id." group by tmp.member_id ") );

        if($results){            
            return floatval($results[0]->total_amt);
        }else{
            return 0;
        }
    }

    public function updateRank($payout){
        $Members=Member::orderBy('level','desc')->get();
        $Ranks=Rank::all();
        $MembersController=new MembersController;
        foreach ($Members as $Member) {
            $children=Member::where('parent_id',$Member->id)->get()->pluck('id')->toArray();
            $personal_pv=$Member->total_personal_pv;

            $squad_plus_affiliate=$this->getSquadPlusAffiliate($Member);
            $squad_plus_affiliate_pinnacle=$this->getSquadPlusAffiliatePinnacle($Member);
            $all_childs=[];
            $counts=array();
            foreach ($children as $child) {
                $child_ids=$MembersController->getChildsOfParent($child);
                $child_ids[]=$child;
                $all_childs=array_merge($all_childs,$child_ids);
                $check_rank=Member::whereIn('id',$child_ids)->get()->pluck('rank_id')->toArray();
                $check_rank=array_unique($check_rank);
                foreach ($check_rank as $check) {
                    $counts[]=$check;
                }                           
            }

            $total_inr_turn_over=Order::whereHas('user.member',function($q)use($all_childs){
                $q->whereIn('id',$all_childs);
            })->whereNotIn('delivery_status',['Order Cancelled','Order Returned'])->sum('final_amount');

            $counts=array_count_values($counts);
            foreach ($Ranks as $Rank) {
                if($Rank->leg_rank){
                
                          
                    foreach ($counts as $key => $value) {   
                        if($Rank->name =='5% Club'){
                            if($Rank->leg_rank===$key && $Rank->leg_rank_count == $value){
                                if($squad_plus_affiliate_pinnacle >= $Rank->bv_to){
                                    $Member->rank_id=$Rank->id;
                                    $Member->save(); 
                                }                           
                                  
                            }
                        }else{     

                            if($Rank->leg_rank===$key && $Rank->leg_rank_count == $value){
                                if($personal_pv >= $Rank->personal_bv_condition && $total_inr_turn_over >= $Rank->bv_from && $squad_plus_affiliate >= $Rank->bv_to){
                                    $Member->rank_id=$Rank->id;
                                    $Member->save(); 
                                }                           
                                  
                            }
                        }
                    }

                }else{

                    if($personal_pv >= $Rank->personal_bv_condition && $total_inr_turn_over >= $Rank->bv_from && $squad_plus_affiliate >= $Rank->bv_to){
                        $Member->rank_id=$Rank->id;
                        $Member->save(); 
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
