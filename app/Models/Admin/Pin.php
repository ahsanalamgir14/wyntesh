<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsTo('App\Models\Admin\Member','owned_by');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Admin\Member','used_by');
    }

    public function request()
    {
        return $this->belongsTo('App\Models\Admin\PinRequest');
    }


}
