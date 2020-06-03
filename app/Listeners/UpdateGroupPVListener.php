<?php

namespace App\Listeners;

use App\Events\UpdateGroupPVEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Admin\MembersLegPv;
use App\Models\Admin\Member;
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
        $member=$user->member;
        $path=$member->path;

        $position=$member->position;

        $uplines=explode('/', $path);        
        $uplines=array_reverse($uplines);
        $uplines=array_filter($uplines, 'strlen');
        
        array_shift($uplines);
        foreach ($uplines as $upline) {
            $MembersLegPv=MembersLegPv::where('member_id',$upline)->where('position',$position)->first();
            $Members=Member::where('id',$upline)->first();

          
            if($MembersLegPv){
                //$MembersLegPv->member_id=$upline;
                //$MembersLegPv->position=$position;
                $MembersLegPv->current_pv+=$order->pv;
                $MembersLegPv->total_pv+=$order->pv;
                $MembersLegPv->save();
            }else{
                $MembersLegPv=new MembersLegPv;
                $MembersLegPv->member_id=$upline;
                $MembersLegPv->position=$position;
                $MembersLegPv->current_pv=$order->pv;
                $MembersLegPv->total_pv=$order->pv;
                $MembersLegPv->save();
            }
            $position=$Members->position;
        }
    }
}
