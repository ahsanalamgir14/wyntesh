<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class MemberPayoutIncome extends Model
{
    protected $table = 'member_payout_incomes';
    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];
    
    public function income()
    {
        return $this->belongsTo('App\Models\Admin\Income');
    }

    public function payout()
    {
        return $this->belongsTo('App\Models\Admin\Payout');
    }

    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member');
    }

}
