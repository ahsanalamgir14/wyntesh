<?php

namespace App\Listeners;

use App\Events\UpdateGroupPVEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Admin\MemberMonthlyLegPv;
use App\Models\Admin\Member;
use Illuminate\Support\Facades\Log;

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
        $path=$member->path;

        $position=$member->position;

        $uplines=explode('/', $path);        
        $uplines=array_reverse($uplines);
        $uplines=array_filter($uplines, 'strlen');
        
        array_shift($uplines);
        $year=date('Y');
        $month=date('m');
        foreach ($uplines as $upline) {
            $MemberMonthlyLegPv=MemberMonthlyLegPv::where('member_id',$upline)
                ->where('position',$position)
                ->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', '=', $month)
                ->first();
            $Members=Member::where('id',$upline)->first();
            Log::info('PV - '.$order->pv);
            if($MemberMonthlyLegPv){
                if($type=='add'){
                    $MemberMonthlyLegPv->pv+=$order->pv;    
                }else if($type=='subtract'){
                    $MemberMonthlyLegPv->pv-=$order->pv;    
                }
                
                $MemberMonthlyLegPv->save();
            }else{
                Log::info('PV - '.$order->pv);
                $MemberMonthlyLegPv=new MemberMonthlyLegPv;
                $MemberMonthlyLegPv->member_id=$upline;
                $MemberMonthlyLegPv->position=$position;
                $MemberMonthlyLegPv->pv=$order->pv;
                $MemberMonthlyLegPv->save();
            }
            $position=$Members->position;
        }
    }
}
