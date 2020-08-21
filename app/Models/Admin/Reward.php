<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    //

    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member');
    }

}
