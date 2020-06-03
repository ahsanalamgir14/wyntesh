<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $table = 'sales';
    public $timestamps = true;

    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member');
    }
}
