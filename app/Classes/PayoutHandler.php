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
        //$this->calculateIncomes();        
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
            $this->updateMemberPayoutSum($memberPayout);                
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
        $Members=Member::whereHas('user',function($q){
            $q->where('is_active',1);
            $q->where('is_blocked',0);
        })->orderBy('level','desc')->get();

        foreach ($Members as $Member) {
            
            $total_payout=0;
            $PayoutIncomes=PayoutIncome::where('payout_id',$this->payout->id)->get();
            $MemberPayout= MemberPayout::where('member_id',$Member->id)->where('payout_id',$this->payout->id)->first();
            
            foreach ($PayoutIncomes as $PayoutIncome) {
                if($PayoutIncome->income->code=='BINARY'){
                    $this->payBinaryIncome($PayoutIncome,$Member,$MemberPayout);            
                }
            }

            $this->updateMemberPayoutSum($MemberPayout);
        }

        $this->updatePayoutSum();
    }

    public function calculateIncomes(){
        $Members=Member::whereHas('user',function($q){
            $q->where('is_active',1);
            $q->where('is_blocked',0);
        })->orderBy('level','desc')->get();

        foreach ($Members as $Member) {
            
            $total_payout=0;
            $PayoutIncomes=PayoutIncome::where('payout_id',$this->payout->id)->get();
            $MemberPayout= MemberPayout::where('member_id',$Member->id)->where('payout_id',$this->payout->id)->first();
            
            foreach ($PayoutIncomes as $PayoutIncome) {
             
                if($PayoutIncome->income->code=='BINARY'){
                    $this->payBinaryIncome($PayoutIncome,$Member,$MemberPayout);            
                }
            }
            $this->updateMemberPayoutSum($MemberPayout);
        }

        $this->updatePayoutSum();
    }

    public function payBinaryIncome($PayoutIncome,$Member,$MemberPayout){

        $member_total_matched_bv=$MemberPayout->total_matched_bv;
        $payout_amount=($member_total_matched_bv*$this->bv_value)/100;

        if($payout_amount==0){
            return;
        }

        $capping=$Member->package->capping_amount;

        if($payout_amount > $capping){
            $payout_amount=$capping;
        }

        $this->addWalletTransaction($MemberPayout,$PayoutIncome,$Member,$payout_amount);
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
