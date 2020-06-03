<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class MembersLegPv extends Model
{
    protected $table = 'members_leg_pv';
    public $timestamps = true;

    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member');
    }
}
