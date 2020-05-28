<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class MemberCarryForwardPv extends Model
{
    protected $table = 'member_carry_forward_pv';
    public $timestamps = true;

    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member');
    }
}
