<?php

namespace App\Listeners;

use App\Events\UpdateGroupPVEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Admin\MembersLegPv;
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
        $sponsor=$member->sponsor_id;
        $path=$member->path;

        $position=$member->position;

        $uplines=explode('/', $path);        
        $uplines=array_reverse($uplines);
        $uplines=array_filter($uplines, 'strlen');
        
        array_shift($uplines);
        $uplines=array_diff( $uplines, [$sponsor] );
        //$date=date('Y-m-d');
        $date=date('Y-m-d',$order->created_at);
        $month=date('m');
        // Log::info($uplines);
        foreach ($uplines as $upline) {
            $MembersLegPv=MembersLegPv::where('member_id',$upline)
                ->where('position',$position)
                ->whereDate('created_at', '=', $date)
                // ->whereMonth('created_at', '=', $month)
                ->first();
            $Members=Member::where('id',$upline)->first();
            // Log::info('PV - '.$order->pv);
            if($MembersLegPv){
                if($type=='add'){
                    $MembersLegPv->pv+=$order->pv;    
                }else if($type=='subtract'){
                    $MembersLegPv->pv-=$order->pv;    
                }
                
                $MembersLegPv->save();
            }else{
                // Log::info('PV - '.$order->pv);
                $MembersLegPv=new MembersLegPv;
                $MembersLegPv->member_id=$upline;
                $MembersLegPv->position=$position;
                $MembersLegPv->pv=$order->pv;
                $MembersLegPv->created_at=$order->created_at;
                $MembersLegPv->save();
            }
            $position=$Members->position;
        }
    }
}
