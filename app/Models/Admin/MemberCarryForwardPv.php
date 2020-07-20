<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class MemberCarryForwardPv extends Model
{
    protected $table = 'member_carry_forword_pvs';
    public $timestamps = true;

    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member');
    }
}
