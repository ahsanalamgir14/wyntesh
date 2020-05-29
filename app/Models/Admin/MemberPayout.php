<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class MemberPayout extends Model
{
    protected $table = 'member_payouts';
    public $timestamps = true;

    public function payout()
    {
        return $this->belongsTo('App\Models\Admin\Payout');
    }

    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member');
    }

}
