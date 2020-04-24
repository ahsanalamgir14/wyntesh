<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    
    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member');
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\Admin\Member');
    }
}
