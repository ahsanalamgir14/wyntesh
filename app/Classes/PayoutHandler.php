<?php

namespace App\Classes;

use App\Models\Admin\Income;
use App\Models\Admin\IncomeParameter;
use App\Models\Admin\Member;
use App\Models\Admin\Rank;
use App\Models\Admin\RankLog;
use App\Models\Admin\CompanySetting;
use App\Models\Admin\Payout;
use App\Models\Admin\PayoutIncome;
use App\Models\Admin\MemberPayout;
use App\Models\Admin\MemberPayoutIncome;
use App\Models\Admin\MembersLegPv;
use App\Models\Admin\AffiliateBonus;
use App\Models\Admin\MemberIncomeHolding;
use App\Models\Admin\IncomeWalletTransactions;
use App\Models\Admin\MemberCarryForwardPv;

use App\Models\Superadmin\TransactionType;

use App\Models\User\Order;
use App\Models\Admin\Sale;

use App\Http\Controllers\User\MembersController;
use Illuminate\Support\Facades\Notification;

use Carbon\Carbon;
use Log;
use DB;

class PayoutHandler 
{    

    public $payout;
    public $tds_percentage=0;
    public $admin_fee_percent=0;

	public function __construct(Payout $payout)
    {
        $this->payout=$payout;
        $this->tds_percentage=CompanySetting::getValue('tds_percentage');
        $this->admin_fee_percent=CompanySetting::getValue('admin_fee_percent');
    }

    public function calculatePayout()
    {
        $this->calculatePayoutSales();
        $this->calculateMatchedBV();
        $this->distributeSquadIncome();
        $this->updatePayoutSum();
        //$this->calculateIncomeParameters();        
        //$this->payIncomes();        
        //$this->updateRank($this->payout);

    }

    public function calculatePayoutSales(){
        //Get total Sales amount/ Total BV Turnover of duration
        $sales_amount=Sale::whereDate('created_at','<=', $this->payout->sales_end_date)
                            ->whereDate('created_at','>=', $this->payout->sales_start_date)
                            ->sum('final_amount_company');

        $total_bv=Sale::whereDate('created_at','<=',$this->payout->sales_end_date)
                        ->whereDate('created_at','>=',$this->payout->sales_start_date)
                        ->sum('pv');

        $total_sales_gst=Sale::whereDate('created_at','<=',$this->payout->sales_end_date)
                        ->whereDate('created_at','>=',$this->payout->sales_start_date)
                        ->sum('gst');

        $total_sales_shipping_fee=Sale::whereDate('created_at','<=',$this->payout->sales_end_date)
                        ->whereDate('created_at','>=',$this->payout->sales_start_date)
                        ->sum('shipping_fee');

        $total_sales_admin_fee=Sale::whereDate('created_at','<=',$this->payout->sales_end_date)
                        ->whereDate('created_at','>=',$this->payout->sales_start_date)
                        ->sum('admin_fee');

        $total_payout_sales_amount=Sale::whereDate('created_at','<=',$this->payout->sales_end_date)
                        ->whereDate('created_at','>=',$this->payout->sales_start_date)
                        ->sum('amount');

        $this->payout->sales_bv=$total_bv;
        $this->payout->sales_amount=$sales_amount;
        $this->payout->sales_gst=$total_sales_gst;
        $this->payout->sales_shipping_fee=$total_sales_shipping_fee;
        $this->payout->sales_admin_fee=$total_sales_admin_fee;
        $this->payout->sales_total_amount=$total_payout_sales_amount;
        $this->payout->save();
    }

    public function calculateMatchedBV(){
        
        $Members=Member::whereHas('user',function($q){
            $q->where('is_active',1);
        })->get();
        // dd($Members);
        $total_mached_bv=0;
        $total_carry_forward_bv=0;

        foreach ($Members as $Member) {
            $MemberPayout=$this->createMemberPayout($Member);
            $MemberPayout=$this->calculateMemberMatchedBV($Member,$MemberPayout);
        }

        $this->payout->total_matched_bv=MemberPayout::where('payout_id',$this->payout->id)->sum('total_matched_bv');
        $this->payout->total_carry_forward_bv=MemberPayout::where('payout_id',$this->payout->id)->sum('total_carry_forward_bv');
        $this->payout->save();
    }

    public function createMemberPayout($Member){
        // Personal Sales amount and BV of Member
        $member_sales_amount=Sale::whereDate('created_at','<=',$this->payout->sales_end_date)
                            ->whereDate('created_at','>=',$this->payout->sales_start_date)
                            ->where('member_id',$Member->id)->sum('final_amount_company');

        $member_total_bv=Sale::whereDate('created_at','<=',$this->payout->sales_end_date)
                                ->whereDate('created_at','>=',$this->payout->sales_start_date)
                                ->where('member_id',$Member->id)->sum('pv');

    
        // Personal Sales amount and BV of Group/Legs
        $member_leg_sales_amount=Sale::whereDate('created_at','<=',$this->payout->sales_end_date)
                                ->whereDate('created_at','>=',$this->payout->sales_start_date)
                                ->whereIn('member_id',$Member->children->pluck('id'))->sum('final_amount_company');

        $member_leg_total_bv=Sale::whereDate('created_at','<=',$this->payout->sales_end_date)
                            ->whereDate('created_at','>=',$this->payout->sales_start_date)
                            ->whereIn('member_id',$Member->children->pluck('id'))->sum('pv');

        // Entry in Member payout
        $MemberPayout=new MemberPayout;
        $MemberPayout->member_id=$Member->id;
        $MemberPayout->payout_id=$this->payout->id;
        $MemberPayout->sales_pv=$member_total_bv;
        $MemberPayout->sales_amount=$member_sales_amount;
        $MemberPayout->group_sales_pv=$member_leg_total_bv;
        $MemberPayout->group_sales_amount=$member_leg_sales_amount;
        $MemberPayout->created_at=$this->payout->sales_end_date;
        $MemberPayout->save();

        return $MemberPayout;
    }

    public function calculateMemberMatchedBV($Member,$memberPayout){
        
        $matched_bv=0;
        $carry_forward=0;
        $carry_forward_position=0;
        $leg_1_pv=0;
        $leg_2_pv=0;

        //Counting Carry forward and Matched points of Member Legs.

        //Getting Member Legs in decenting based on current PV
        $legs=0;
        $legs= MembersLegPv::addSelect(['*', \DB::raw('sum(pv) as totalPv')])
                    ->whereDate('created_at','<=',$this->payout->sales_end_date)
                    ->whereDate('created_at','>=',$this->payout->sales_start_date)
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
            $MemberCarryForwardPv->payout_id            =$this->payout->id;
            $MemberCarryForwardPv->position             =$carry_forward_position;
            $MemberCarryForwardPv->pv                   =$carry_forward;
            $MemberCarryForwardPv->save();
        }
     
        // Count total of all values;
        $matched_bv = floatval($matched_bv)/24;
       
        // Save Matched bv and total carry_forward to member payout.
        $Member->total_matched_bv+=$matched_bv;
        $Member->save();
        
        $memberPayout->total_matched_bv=$matched_bv;
        $memberPayout->total_carry_forward_bv=$carry_forward;
        $memberPayout->save();
    }

    public function distributeSquadIncome(){
        $income=Income::where('code','SQUAD')->first();
        
        $payoutIncome=PayoutIncome::where('income_id',$income->id)->where('payout_id',$this->payout->id)->first();

        $this->calculateSquadIncomeFactor($income,$payoutIncome);
        
        $Members=Member::whereHas('user',function($q){
            $q->where('is_active',1);
        })->orderBy('level','desc')->get();

        foreach ($Members as $Member) {
            $memberPayout = MemberPayout::where('member_id',$Member->id)->where('payout_id',$this->payout->id)->first();
            $this->paySquadIncome($memberPayout,$payoutIncome);        
        }
    }

    public function calculateSquadIncomeFactor($income,$payoutIncome) {
      
        $monthly_company_turnover_percent=0;
        
        foreach ($income->income_parameters as $parameter) {
            if($parameter->name=='monthly_company_turnover_percent'){
                $monthly_company_turnover_percent=$parameter->value_1;
            }
        }

        // Counting matching point value based on parameters and plan criteria
        $payoutIncome->income_payout_parameter_1_name='sbp';
        if($this->payout->total_matched_bv==0){
            $income_factor=0;
        }else{
            $income_factor=(($this->payout->sales_bv*$monthly_company_turnover_percent)/100)/$this->payout->total_matched_bv;    
        }
        
        $payoutIncome->income_payout_parameter_1_value=round($income_factor,4);
        $payoutIncome->save();
    }

    public function paySquadIncome($memberPayout,$payoutIncome) {

        $totalIncomeValue = 0;

        $factor = $payoutIncome->income_payout_parameter_1_value;
        $payout_amount = $memberPayout->total_matched_bv*$factor;

        if(!$payout_amount)
            return;

        $this->addWalletTransaction($memberPayout,$payoutIncome,$memberPayout->member,$payout_amount);

    }

    public function calculateIncomeParameters(){
        $PayoutIncomes=PayoutIncome::where('payout_id',$this->payout->id)->get();
        foreach ($PayoutIncomes as $PayoutIncome) {
                        
            if($PayoutIncome->income->code=='ELEVATION'){
                $this->calculateElevationIncomeFactor($PayoutIncome->income,$PayoutIncome);
            }

            if($PayoutIncome->income->code=='LUXURY'){
                $this->calculateLuxuryIncomeFactor($PayoutIncome->income,$PayoutIncome);
            }

            if($PayoutIncome->income->code=='PREMIUM'){
                $this->calculatePremiumIncomeFactor($PayoutIncome->income,$PayoutIncome);
            }
        }
    }

    public function payIncomes(){
        $Members=Member::whereHas('user',function($q){
            $q->where('is_active',1);
        })->orderBy('level','desc')->get();

        foreach ($Members as $Member) {
            
            $PayoutIncomes=PayoutIncome::where('payout_id',$this->payout->id)->get();
            $memberPayout= MemberPayout::where('member_id',$Member->id)->where('payout_id',$this->payout->id)->first();

            foreach($PayoutIncomes as $PayoutIncome){
               
                if($income->code=='ELEVATION'){
                    $this->payElevationIncome($PayoutIncome,$memberPayout);
                }

                if($income->code=='LUXURY'){
                    $this->payLuxuryIncome($PayoutIncome,$memberPayout);
                }

                if($income->code=='PREMIUM'){
                    $this->payPremiumIncome($PayoutIncome,$memberPayout);
                }
            }            
        }
    }

    public function calculateElevationIncomeFactor($income,$PayoutIncome) {
        $monthly_company_turnover_percent=0;
        
        foreach ($income->income_parameters as $parameter) {
            if($parameter->name=='monthly_company_turnover_percent'){
                $monthly_company_turnover_percent=$parameter->value_1;
            }
        }

        // Counting matching point value based on parameters and plan criteria
        $PayoutIncome->income_payout_parameter_1_name='ebp';
        
        $eb_eligibles=$this->getElevationEligibles($income,$this->payout);
        $payout_month=Carbon::createFromFormat('Y-m-d', $this->payout->sales_start_date)->format('m');
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

    public function payElevationIncome($payoutIncome,$memberPayout) {

        $totalIncomeValue = 0;
        
        $factor = $payoutIncome->income_payout_parameter_1_value;
        
        $eb_eligibles=$this->getElevationEligibles($payoutIncome->income,$this->payout);

        if(!in_array($memberPayout->member->id, $eb_eligibles)){
            return 0;
        }

        $payout_month=Carbon::createFromFormat('Y-m-d', $this->payout->sales_start_date)->format('m');
        $month_payouts=Payout::whereMonth('sales_start_date',$payout_month)->get()->pluck('id')->toArray();
        $member_matched=MemberPayout::where('member_id',$memberPayout->member->id)->whereIn('payout_id',$month_payouts)->sum('total_matched_bv');

        $payout_amount = $member_matched*$factor;

        if(!$payout_amount)
            return;

        $this->addWalletTransaction($memberPayout,$payoutIncome,$memberPayout->member,$payout_amount);
    }

    public function calculateLuxuryIncomeFactor($income,$PayoutIncome) {
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
        $payout_month=Carbon::createFromFormat('Y-m-d', $this->payout->sales_start_date)->format('m');
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

    public function payLuxuryIncome($payoutIncome,$memberPayout) {

        $totalIncomeValue = 0;
        $factor = $payoutIncome->income_payout_parameter_1_value;

        $accumulating_rank=2;
        $disbursal_rank=8;

        foreach ($payoutIncome->income->income_parameters as $parameter) {            
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

        $payout_month=Carbon::createFromFormat('Y-m-d', $this->payout->sales_start_date)->format('m');
        $month_payouts=Payout::whereMonth('sales_start_date',$payout_month)->get()->pluck('id')->toArray();
        $member_matched=MemberPayout::where('member_id',$memberPayout->member->id)->whereIn('payout_id',$month_payouts)->sum('total_matched_bv');

        $payout_amount = $member_matched*$factor;

        if(!$payout_amount)
            return;

        $this->addWalletTransaction($memberPayout,$payoutIncome,$memberPayout->member,$payout_amount);

    }

    public function calculatePremiumIncomeFactor($income,$PayoutIncome) {
        $monthly_company_turnover_percent=0;
        
        foreach ($income->income_parameters as $parameter) {
            if($parameter->name=='monthly_company_turnover_percent'){
                $monthly_company_turnover_percent=$parameter->value_1;
            }
        }

        // Counting matching point value based on parameters and plan criteria
        $PayoutIncome->income_payout_parameter_1_name='pbp';
        $pb_eligibles_and_points=$this->getPremiumEligibles($income,$this->payout);
        $total_points_collected=array_sum(array_values($pb_eligibles_and_points));
        $pb_eligibles=array_keys($pb_eligibles_and_points);   

        $payout_month=Carbon::createFromFormat('Y-m-d', $this->payout->sales_start_date)->format('m');      
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

    public function payPremiumIncome($payoutIncome,$memberPayout) {

        $totalIncomeValue = 0;
        $factor = $payoutIncome->income_payout_parameter_1_value;

        $pb_eligibles_and_points=$this->getPremiumEligibles($payoutIncome->income,$this->payout);

        $pb_eligibles=array_keys($pb_eligibles_and_points);

        if(!in_array($memberPayout->member->id, $pb_eligibles)){
            return 0;
        }

        $payout_amount = $pb_eligibles_and_points[$memberPayout->member->id]*$factor;
        if(!$payout_amount)
            return;

        $this->addWalletTransaction($memberPayout,$payoutIncome,$memberPayout->member,$payout_amount);
    }

    public function updateMemberPayoutSum($MemberPayout){        
        $total_member_tds=MemberPayoutIncome::where('payout_id',$MemberPayout->payout_id)->where('member_id',$MemberPayout->member_id)->sum('tds');

        $total_member_admin_fee=MemberPayoutIncome::where('payout_id',$MemberPayout->payout_id)->where('member_id',$MemberPayout->member_id)->sum('admin_fee');

        $total_member_payout_amount=MemberPayoutIncome::where('payout_id',$MemberPayout->payout_id)->where('member_id',$MemberPayout->member_id)->sum('payout_amount');

        $total_net_payable_amount=MemberPayoutIncome::where('payout_id',$MemberPayout->payout_id)->where('member_id',$MemberPayout->member_id)->sum('net_payable_amount');

        $MemberPayout->payout_amount=$total_member_payout_amount;
        $MemberPayout->tds=$total_member_tds;
        $MemberPayout->admin_fee=$total_member_admin_fee;
        $MemberPayout->net_payable_amount=$total_net_payable_amount;
        $MemberPayout->save();
        $MemberPayout->member->current_personal_pv=0;
        $MemberPayout->member->save();

        if($MemberPayout->net_payable_amount != 0){
            try{
               // Log::info($MemberPayout->net_payable_amount);
                //Notification::send($MemberPayout->member->user, new PayoutNotification($MemberPayout));
            }catch(\Exception $e)
            {
            }
        }
    }

    public function updatePayoutSum(){        
        $PayoutIncomes=PayoutIncome::where('payout_id',$this->payout->id)->get();
        // Calculating income wise total payout.
        foreach ($PayoutIncomes as $PayoutIncome) {
            $IncomePayout= MemberPayoutIncome::select([                
                DB::raw("SUM(payout_amount) as total_payout_amount"),
                DB::raw("SUM(tds) as total_tds"),
                DB::raw("SUM(admin_fee) as total_admin_fee"),
                DB::raw("SUM(net_payable_amount) as total_net_payable_amount"),
            ])->where('payout_id',$PayoutIncome->payout_id)->where('income_id',$PayoutIncome->income_id)->groupBy('payout_id')->first();

            if($IncomePayout){                
                $PayoutIncome->payout_amount=$IncomePayout->total_payout_amount;
                $PayoutIncome->tds=$IncomePayout->total_tds;
                $PayoutIncome->admin_fee=$IncomePayout->total_admin_fee;
                $PayoutIncome->tds_percent=$this->tds_percentage;
                $PayoutIncome->admin_fee_percent=$this->admin_fee_percent;
                $PayoutIncome->net_payable_amount=$IncomePayout->total_net_payable_amount;
                $PayoutIncome->save();
            }

        }

        $TotalPayout=MemberPayoutIncome::select([                
            DB::raw("SUM(payout_amount) as total_payout_amount"),
            DB::raw("SUM(tds) as total_tds"),
            DB::raw("SUM(admin_fee) as total_admin_fee"),
            DB::raw("SUM(net_payable_amount) as total_net_payable_amount"),
        ])->where('payout_id',$this->payout->id)->groupBy('payout_id')->first();

        if($TotalPayout){            
            $this->payout->payout_amount=$TotalPayout->total_payout_amount;
            $this->payout->tds=$TotalPayout->total_tds;
            $this->payout->admin_fee=$TotalPayout->total_admin_fee;
            $this->payout->net_payable_amount=$TotalPayout->total_net_payable_amount;
        }

        $this->payout->ended_at=Carbon::now();
        $this->payout->save();
    }

    public function getSquadPlusAffiliate($Member){
        $income=MemberPayoutIncome::whereIn('income_id',[2,3,8])->where('member_id',$Member->id)->sum('payout_amount');
        return $income;
    }

    public function getSquadPlusAffiliatePinnacle($Member){
        $income=MemberPayoutIncome::where('member_id',$Member->id)->sum('payout_amount');
        return $income;
    }

    public function getSquadAffiliateEligible($from,$to,$criteria,$rank){
        
        $eligible_rank_members=Member::where('rank_id',$rank)->get()->pluck('id')->toArray();

        $eligibles=MemberPayoutIncome::select([ DB::raw("SUM(payout_amount) as total_payout_amount"),DB::raw("member_id")])
                ->havingRaw('total_payout_amount >= ?',[$criteria])
                ->whereDate('created_at','>=', $from)
                ->whereDate('created_at','<=', $to)
                ->whereIn('member_id',$eligible_rank_members)
                ->groupBy('member_id')
                ->get()->pluck('member_id');

        return $eligibles;
    }

    public function calulatePremiumPoints($Member,$from,$to){
        $income=MemberPayoutIncome::whereIn('income_id',[2,3,8])
            ->whereDate('created_at','>=', $from)
            ->whereDate('created_at','<=', $to)
            ->where('member_id',$Member->id)
            ->sum('payout_amount');
        return $income;
    }

    public function addWalletTransaction($MemberPayout,$PayoutIncome,$Member,$payout_amount){

        $net_payable_amount=0;
        $income_tds=0;
        $income_admin_fee=0;

        $net_payable_amount         = $payout_amount;
        $income_tds                 = floatval(($net_payable_amount*$this->tds_percentage)/100);
        $income_admin_fee           = floatval(($net_payable_amount*$this->admin_fee_percent)/100);
        $net_payable_amount         = $net_payable_amount-$income_tds-$income_admin_fee;

        $MemberPayoutIncome=new MemberPayoutIncome;
        $MemberPayoutIncome->member_payout_id                = $MemberPayout->id;
        $MemberPayoutIncome->payout_id                       = $this->payout->id;
        $MemberPayoutIncome->income_id                       = $PayoutIncome->income_id;
        $MemberPayoutIncome->member_id                       = $Member->id;
        $MemberPayoutIncome->income_payout_parameter_1_name  = $PayoutIncome->income_payout_parameter_1_name;
        $MemberPayoutIncome->income_payout_parameter_1_value = $PayoutIncome->income_payout_parameter_1_value;
        $MemberPayoutIncome->payout_amount                   = $payout_amount;
        $MemberPayoutIncome->tds                             = $income_tds;
        $MemberPayoutIncome->admin_fee                       = $income_admin_fee;
        $MemberPayoutIncome->tds_percent                     = $this->tds_percentage;
        $MemberPayoutIncome->admin_fee_percent               = $this->admin_fee_percent;
        $MemberPayoutIncome->net_payable_amount              = $net_payable_amount;
        $MemberPayoutIncome->save();

        $TransactionType=TransactionType::where('name',$PayoutIncome->income->name)->first();
        $IncomeWalletTransactions=new IncomeWalletTransactions;
        $IncomeWalletTransactions->member_id           = $Member->id;
        $IncomeWalletTransactions->amount              = $net_payable_amount;
        $IncomeWalletTransactions->balance             = $net_payable_amount+$Member->income_wallet_balance;
        $IncomeWalletTransactions->transaction_type_id = $TransactionType->id;
        $IncomeWalletTransactions->transfered_to       = $Member->user->id;
        $IncomeWalletTransactions->note                = $PayoutIncome->income->name;
        $IncomeWalletTransactions->save(); 

        $Member->income_wallet_balance+=$net_payable_amount;
        $Member->save(); 

    }

}
