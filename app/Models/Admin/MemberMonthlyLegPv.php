<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class MemberMonthlyLegPv extends Model
{
    protected $table = 'member_monthly_leg_pvs';
    public $timestamps = true;

    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member');
    }
}
