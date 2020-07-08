<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\RankLog;

class MemberPayout extends Model
{
    protected $table = 'member_payouts';
    public $timestamps = true;
    protected $appends = ['rank'];
    public function payout()
    {
        return $this->belongsTo('App\Models\Admin\Payout');
    }

    public function getRankAttribute()
    {
        $Rank=RankLog::where('payout_id',$this->payout_id)->where('member_id',$this->member_id)->first();
        if($Rank){
            return $Rank->rank;    
        }else{
            return '';
        }
        

    }

    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member');
    }  

}
