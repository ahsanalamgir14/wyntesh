<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MatchingPoint extends Model
{
    protected $table = 'matching_points';
    public $timestamps = true;


    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member')->with('user');
    }

}
