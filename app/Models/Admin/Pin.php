<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pin extends Model
{
    protected $table = 'pins';
    public $timestamps = true;
    use SoftDeletes;

    public function package()
    {
        return $this->belongsTo('App\Models\Admin\Package');
    }

    public function owner()
    {
        return $this->belongsTo('App\Models\Admin\Member','owned_by')->with('user');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Admin\Member','used_by')->with('user');
    }

    public function request()
    {
        return $this->belongsTo('App\Models\Admin\PinRequest');
    }


}
