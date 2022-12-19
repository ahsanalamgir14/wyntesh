<?php

namespace App\Jobs;

use DB;
use App\Models\Admin\Sale;
use App\Models\User\Order;
use App\Models\Admin\Income;
use App\Models\Admin\Member;
use App\Models\Admin\Reward;

use Illuminate\Bus\Queueable;
use App\Models\Admin\MemberPayout;
use App\Models\Admin\MembersLegPv;
use App\Models\Admin\PayoutIncome;
use App\Models\Admin\MatchingPoint;
use App\Models\Admin\AffiliateBonus;
use App\Models\Admin\CompanySetting;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Admin\MemberCarryForwardPv;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;


class ProcessTestMatching implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $from_date;
    public $to_date;
    public $total_matched_bv = 0 ;
    public $squad_capping_matched_bv=0;
    public $luxury_capping_matched_bv=0;
    
    public function __construct($from_date,$to_date)
    {
        $this->from_date = $from_date;
        $this->to_date = $to_date;
        $this->squad_capping_matched_bv=CompanySetting::getValue('squad_capping_matched_bv');
        $this->luxury_capping_matched_bv=CompanySetting::getValue('luxury_capping_matched_bv');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->generateMatchingPoints($this->from_date,$this->to_date);
        $this->calculateIncomeds();
    }

    public function generateMatchingPoints($from_date,$to_date){

        $Members=Member::whereHas('user',function($q){
            $q->where('is_active',1);
            $q->where('is_blocked',0);
        })->get();

        //DB::statement('TRUNCATE matching_points');

                $total_bv=Sale::whereDate('created_at','<=',$to_date)
                    ->whereDate('created_at','>=',$from_date)
                    ->sum('pv');

        foreach ($Members as $Member) {
            $this->calculateMemberMatchedBV($Member,$total_bv,$from_date,$to_date);
        }
    }
    
    public function calculateMemberMatchedBV($Member,$total_bv,$from_date,$to_date){
        
        $matched_bv=0;
        $carry_forward=0;
        $carry_forward_position=0;
        $leg_1_pv=0;
        $leg_2_pv=0;
        
        //Counting Carry forward and Matched points of Member Legs.
        //Getting Member Legs in decenting based on current PV
        
        $legs=0;
        $legs= MembersLegPv::addSelect(['*', \DB::raw('sum(pv) as totalPv')])
                    ->whereDate('created_at','>=',$from_date)
                    ->whereDate('created_at','<=',$to_date)
                    ->where('member_id',$Member->id)
                    ->orderBy('totalPv','desc')
                    ->groupBy('position')
                    ->get()->pluck('totalPv','position')->toArray();
        
        $MatchingPoint=new MatchingPoint;
        $MatchingPoint->member_id=$Member->id;    
        $MatchingPoint->total_sales=$total_bv;    
        $MatchingPoint->save();

        $last_carry_forward=MemberCarryForwardPv::where('member_id',$Member->id)->orderBy('payout_id','desc')->first();

        if($last_carry_forward){

            $MatchingPoint->previous_carry_forward=$last_carry_forward->pv;
            $MatchingPoint->previous_carry_forward_position=$last_carry_forward->position;
            $MatchingPoint->save();

            if(count($legs)){
                $exsting_pv=intval(isset($legs[$last_carry_forward->position])?$legs[$last_carry_forward->position]:0);
                $legs[$last_carry_forward->position]=$exsting_pv+$last_carry_forward->pv;
            }
        }


            if(isset($legs[1])){
                $MatchingPoint->leg_1=$legs[1];
            }

            if(isset($legs[2])){
                $MatchingPoint->leg_2=$legs[2];
            }

            if(isset($legs[3])){
                $MatchingPoint->leg_3=$legs[3];
            }

            if(isset($legs[4])){
                $MatchingPoint->leg_4=$legs[4];
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
        $matched_bv = floatval($matched_bv)/24;
       
        $MatchingPoint->matched=$matched_bv;
        $MatchingPoint->capping_matched=$matched_bv;
        $MatchingPoint->luxury_capping_matched=$matched_bv;
        $MatchingPoint->carry_forward=$carry_forward;
        $MatchingPoint->carry_forward_position=$carry_forward_position;
        $MatchingPoint->save();
        $this->total_matched_bv+=$matched_bv;
        if($MatchingPoint->capping_matched > $this->squad_capping_matched_bv){
            $MatchingPoint->capping_matched = $this->squad_capping_matched_bv;
            $MatchingPoint->save();
        }
        if($MatchingPoint->luxury_capping_matched > $this->luxury_capping_matched_bv){
            $MatchingPoint->luxury_capping_matched = $this->luxury_capping_matched_bv;
            $MatchingPoint->save();
        }
    }

    public function calculateIncomeds(){
        $Members=Member::whereHas('user',function($q){
            $q->where('is_active',1);
            $q->where('is_blocked',0);
        })->orderBy('level','desc')->get();

        foreach ($Members as $member) {
            
            $incomes=Income::all();
            $MatchingPoint=MatchingPoint::where('member_id',$member->id)->first();
            foreach ($incomes as $income) {
                        
                if($income->code=='REWARD'){
                    $this->calculateRewardIncome($MatchingPoint, $member, $income);
                }
                if($income->code=='AFFILIATE'){
                    $this->calculateAffiliateIncome($MatchingPoint, $member, $income);
                }
                if($income->code=='SQUAD'){
                    $this->calculateSquadIncome($MatchingPoint, $member, $income);
                }
                if($income->code=='ELEVATION'){
                    $this->calculateElevationIncome($MatchingPoint, $member, $income);
                }
                if($income->code=='LUXURY'){
                    $this->calculateLuxuryIncome($MatchingPoint, $member, $income);
                }
                if($income->code=='PREMIUM'){
                    $this->calculatePremiumIncome($MatchingPoint, $member, $income);
                }
    
            }       
        }
    }

    public function calculateRewardIncome($MatchingPoint, $Member ,$income){
        
        $Rewards=Reward::select([
                DB::raw('sum(amount) as total_payout_amount'),
                DB::raw('sum(tds_amount) as total_tds'),
                DB::raw('sum(final_amount) as total_net_payable_amount'),
                DB::raw("tds_percent")
            ])->where('member_id',$Member->id)
            ->whereDate('created_at','<=', $this->from_date)
            ->whereDate('created_at','>=', $this->to_date)
            ->first();

        if($Rewards->total_payout_amount > 0){
            $MatchingPoint->income_1_payout_amount=$Rewards->total_payout_amount;
            $MatchingPoint->save();
        }
    }

    public function calculateAffiliateIncome($MatchingPoint, $Member ,$income){
        
        $AffiliateBonus= AffiliateBonus::select([                
                DB::raw("SUM(amount) as total_payout_amount"),
                DB::raw("SUM(tds_amount) as total_tds"),
                DB::raw("SUM(final_amount) as total_net_payable_amount"),
                DB::raw("tds_percent"),
            ])->where('member_id',$Member->id)
            ->whereDate('created_at','<=', $this->from_date)
            ->whereDate('created_at','>=', $this->to_date)
            ->first();

        if($AffiliateBonus->total_payout_amount > 0){
            $MatchingPoint->income_2_payout_amount=$AffiliateBonus->total_payout_amount;
            $MatchingPoint->save();
        }
    }

    public function calculateSquadIncome($MatchingPoint, $Member, $income) {
      
        $income_percent=0;
       
        foreach ($income->income_parameters as $parameter) {
            if($parameter->name=='income_percent'){
                $income_percent=$parameter->value_1;
            }
        }

        $income_factor=0;
        if($this->total_matched_bv==0){
            $income_factor=0;
        }else{
            $income_factor=(($MatchingPoint->total_sales*$income_percent)/100)/$this->total_matched_bv;    
        }

        $payout_amount = $MatchingPoint->capping_matched*$income_factor;

        if(!$payout_amount)
            return;
        
        $MatchingPoint->income_3_factor_value = round($income_factor,4);
        $MatchingPoint->income_3_total_points = $MatchingPoint->total_sales;
        $MatchingPoint->income_3_point_value =  $MatchingPoint->capping_matched;
        $MatchingPoint->income_3_payout_amount = $payout_amount;
        $MatchingPoint->save();
    }

    public function calculateElevationIncome($MatchingPoint, $Member, $income){

        $income_percent=0;
        $minimum_matched=0;
        $minimum_rank=0;
        $income_factor=0; 

        foreach ($income->income_parameters as $parameter) {
            if($parameter->name=='income_percent'){
                $income_percent=$parameter->value_1;
            }
            if($parameter->name=='minimum_matched'){
                $minimum_matched=$parameter->value_1;
            }
            if($parameter->name=='minimum_rank'){
                $minimum_rank=(int)$parameter->value_1;
            }
        }

        if($Member->rank_id < $minimum_rank){
            return;
        }

        $TotalMatchedBv=MatchingPoint::addSelect([ DB::raw("sum((matched DIV ".$minimum_matched.")) as total_points")])
                ->where('matched','>=',$minimum_matched)
                ->whereHas('member',function($q)use($minimum_rank){
                    $q->where('rank_id','>=',$minimum_rank);
                })
                ->first();

        $total_points=$TotalMatchedBv->total_points;

        if(!$total_points){
            $income_factor=0;
        }else{            
            $income_factor=(($MatchingPoint->total_sales*$income_percent)/100)/$total_points;
        }

        $points=intdiv($MatchingPoint->matched,$minimum_matched);
        $payout_amount=($points*$income_factor);

        if($payout_amount==0){
            return;
        }

        $MatchingPoint->income_4_factor_value = round($income_factor,4);
        $MatchingPoint->income_4_total_points = $total_points;
        $MatchingPoint->income_4_point_value = $points;
        $MatchingPoint->income_4_payout_amount = $payout_amount;
        $MatchingPoint->save();
    }

    public function calculateLuxuryIncome($MatchingPoint, $Member, $income){

        $income_percent=0;
        $minimum_matched=0;
        $minimum_rank=0;
        $income_factor=0; 

        foreach ($income->income_parameters as $parameter) {
            if($parameter->name=='income_percent'){
                $income_percent=$parameter->value_1;
            }
            if($parameter->name=='minimum_matched'){
                $minimum_matched=$parameter->value_1;
            }
            if($parameter->name=='minimum_rank'){
                $minimum_rank=(int)$parameter->value_1;
            }
        }

        if($Member->rank_id < $minimum_rank){
            return;
        }

        $TotalMatchedBv=MatchingPoint::addSelect([ DB::raw("sum((luxury_capping_matched DIV ".$minimum_matched.")) as total_points")])
                ->where('luxury_capping_matched','>=',$minimum_matched)
                ->whereHas('member',function($q)use($minimum_rank){
                    $q->where('rank_id','>=',$minimum_rank);
                })
                ->first();

        $total_points=$TotalMatchedBv->total_points;

        if(!$total_points){
            $income_factor=0;
        }else{            
            $income_factor=(($MatchingPoint->total_sales*$income_percent)/100)/$total_points;
        }

        $points=intdiv($MatchingPoint->luxury_capping_matched,$minimum_matched);
        $payout_amount=($points*$income_factor);

        if($payout_amount==0){
            return;
        }

        $MatchingPoint->income_5_factor_value = round($income_factor,4);
        $MatchingPoint->income_5_total_points = $total_points;
        $MatchingPoint->income_5_point_value = $points;
        $MatchingPoint->income_5_payout_amount = $payout_amount;
        $MatchingPoint->save();
    }

    public function calculatePremiumIncome($MatchingPoint, $Member, $income){
        $income_percent=0;
        $minimum_matched=0;
        $minimum_rank=0;
        $income_factor=0;

        foreach ($income->income_parameters as $parameter) {
            if($parameter->name=='income_percent'){
                $income_percent=$parameter->value_1;
            }
            if($parameter->name=='minimum_matched'){
                $minimum_matched=$parameter->value_1;
            }
            if($parameter->name=='minimum_rank'){
                $minimum_rank=$parameter->value_1;
            }
        }
        if($Member->rank_id < $minimum_rank){
            return;
        }

        $points_array=MatchingPoint::addSelect([ DB::raw("sum((matched)) as total_points")])
                    ->whereHas('member',function($q)use($minimum_rank){ $q->where('rank_id','>=',$minimum_rank);})
                    ->whereRank('member.rank_logs', $minimum_rank, $this->from_date)
                    ->groupBy('member_id')
                    ->having('total_points','>=',$minimum_matched)->get()->pluck('total_points')->toArray();

        $total_points=0;

        foreach ($points_array as $key => $value) {
            $points=intdiv($value,$minimum_matched);
            if($points >= 1){
                $total_points+=$points;
            }
        }

        $monthly_total_bv=Sale::whereDate('created_at','<=', $this->from_date)
                    ->whereDate('created_at','>=', $this->to_date)
                    ->sum('pv');

        if(!$total_points){
            $income_factor=0;
        }else{            
            $income_factor=(($monthly_total_bv*$income_percent)/100)/$total_points;
        }

        $points=intdiv($MatchingPoint->matched,$minimum_matched);
        $payout_amount=($points*$income_factor);

        if($payout_amount==0){
            return;
        }

        $MatchingPoint->income_6_factor_value = round($income_factor,4);
        $MatchingPoint->income_6_total_points = $total_points;
        $MatchingPoint->income_6_point_value = $points;
        $MatchingPoint->income_6_payout_amount = $payout_amount;
        $MatchingPoint->save();
    }
}
