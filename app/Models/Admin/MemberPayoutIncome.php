<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\MemberPayout;

class MemberPayoutIncome extends Model
{
    protected $table = 'member_payout_incomes';
    public $timestamps = true;
    protected $appends = ['group_pv'];
    protected $dates = ['created_at', 'updated_at'];
    
    public function income()
    {
        return $this->belongsTo('App\Models\Admin\Income');
    }

    public function payout()
    {
        return $this->belongsTo('App\Models\Admin\Payout');
    }

    public function getGroupPvAttribute()
    {
        $group_pv=0;
        $MemberPayout=MemberPayout::where('payout_id',$this->payout_id)->where('member_id',$this->member_id)->first();
        if($MemberPayout){
            $group_pv=$MemberPayout->group_sales_pv?$MemberPayout->group_sales_pv:0;
        }
        return $group_pv;
    }

    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member');
    }

}
