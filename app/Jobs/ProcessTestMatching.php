<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Admin\Member;
use App\Models\Admin\MembersLegPv;
use App\Models\Admin\MatchingPoint;
use App\Models\Admin\MemberCarryForwardPv;
use App\Models\User\Order;
use DB;


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
    
    public function __construct($from_date,$to_date)
    {
        $this->from_date = $from_date;
        $this->to_date = $to_date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->generateMatchingPoints($this->from_date,$this->to_date);
    }

    public function generateMatchingPoints($from_date,$to_date){

        $Members=Member::whereHas('user',function($q){
            $q->where('is_active',1);
            $q->where('is_blocked',0);
        })->get();

        //DB::statement('TRUNCATE matching_points');


        $total_sales=Order::whereNotIn('delivery_status',['Order Cancelled','Order Returned'])->whereDate('created_at','<=',$to_date)
                        ->whereDate('created_at','>=',$from_date)
                        ->sum('pv');

        foreach ($Members as $Member) {
            $this->calculateMemberMatchedBV($Member,$total_sales,$from_date,$to_date);
        }
    }

    public function calculateMemberMatchedBV($Member,$total_sales,$from_date,$to_date){
        
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
        $MatchingPoint->total_sales=$total_sales;    
            
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

        $MatchingPoint->matched=floatval($matched_bv/24);
        $MatchingPoint->carry_forward=$carry_forward;
        $MatchingPoint->carry_forward_position=$carry_forward_position;
        $MatchingPoint->save();
    }
}
