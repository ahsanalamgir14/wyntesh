<?php

namespace App\Listeners;

use App\Events\UpdateGroupPVEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Admin\MembersLegPv;
use App\Models\Admin\MemberCarryForwardPv;
use App\Models\Admin\Member;
use App\Models\Admin\Contest;
use App\Models\Admin\ContestMember;
use Illuminate\Support\Facades\Log;
use DB, Carbon\Carbon;

class UpdateGroupPVListener implements ShouldQueue
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
     * @param  UpdateGroupPVEvent  $event
     * @return void
     */
    public function handle(UpdateGroupPVEvent $event)
    {
        $order=$event->order;
        $user=$event->user;
        $type=$event->type;
        $member=$user->member;
        $sponsor=$member->sponsor_id;
        $path=$member->path;

        $position=$member->position;

        $uplines=explode('/', $path);        
        $uplines=array_reverse($uplines);
        $uplines=array_filter($uplines, 'strlen');
        
        array_shift($uplines);
        
        $date=date('Y-m-d');
        //$dt=date_create($order->created_at);
        //$date= date_format($dt,"Y-m-d");

        foreach ($uplines as $upline) {

            $Members=Member::where('id',$upline)->first();

            if($upline==$sponsor){
                $position=$Members->position;
                continue;
            }

            $MembersLegPv=MembersLegPv::where('member_id',$upline)
                ->where('position',$position)
                ->whereDate('created_at', '=', $date)
                // ->whereMonth('created_at', '=', $month)
                ->first();
            // Log::info('PV - '.$order->pv);
            if($MembersLegPv){
                if($type=='add'){
                    $MembersLegPv->pv+=$order->pv;    
                }else if($type=='subtract'){
                    $MembersLegPv->pv-=$order->pv;    
                }
                $MembersLegPv->save();
                $this->updateContestPoints($MembersLegPv->member);
            }else{

                if($type=='add'){
                    $MembersLegPv=new MembersLegPv;
                    $MembersLegPv->member_id=$upline;
                    $MembersLegPv->position=$position;
                    $MembersLegPv->pv=$order->pv;
                    $MembersLegPv->created_at=$order->created_at;
                    $MembersLegPv->save();
                }elseif($type=='subtract'){
                    $MembersLegPv=new MembersLegPv;
                    $MembersLegPv->member_id=$upline;
                    $MembersLegPv->position=$position;
                    $MembersLegPv->pv=-$order->pv;
                    $MembersLegPv->created_at=$order->created_at;
                    $MembersLegPv->save();
                }
            }
            
            $position=$Members->position;
            $this->updateContestPoints($MembersLegPv->member);
        }
    }

    public function updateContestPoints($member)
    {
        $contest=Contest::where('is_current',1)->first();

        $today=Carbon::today();

        if($today->gt($contest->end_date)){
            return; 
        }

        $legs= MembersLegPv::addSelect(['*', \DB::raw('sum(pv) as totalPv')])
                ->whereDate('created_at','>=',$contest->start_date)
                ->whereDate('created_at','<=',$contest->end_date)
                ->where('member_id',$member->id)
                ->orderBy('totalPv','desc')
                ->groupBy('position')
                ->get()->pluck('totalPv','position')->toArray();

        $last_carry_forward=MemberCarryForwardPv::where('member_id',$member->id)->orderBy('payout_id','desc')->first();
        \Log::info($last_carry_forward);
        \Log::info($member->id);
        if($last_carry_forward){
                $exsting_pv=intval(isset($legs[$last_carry_forward->position])?$legs[$last_carry_forward->position]:0);
                $legs[$last_carry_forward->position]=$exsting_pv+$last_carry_forward->pv;
        }

        arsort($legs);

        $index = 0;
        $leg_1_pv=0;
        $leg_2_pv=0;
        $matched_bv=0;
        foreach ($legs as $position => $pv) {
            if($index==0){
                $leg_1_pv=$pv;
               
                $index++;
                continue;
            }
            if($index==1){
                $leg_2_pv=$pv;                
            }
            // Add current pv to matched_bv of all legs except strong one.
            $matched_bv+=$pv;
            $index++;
        }

        $contestMember=ContestMember::where('member_id',$member->id)->where('contest_id',$contest->id)->first();

        if($contestMember){
            $contestMember->points=$matched_bv;
            $contestMember->save();
        }
    }
}
