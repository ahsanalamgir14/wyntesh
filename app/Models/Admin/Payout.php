<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $table = 'payouts';
    public $timestamps = true;

    public function payout_type()
    {
        return $this->belongsTo('App\Models\Admin\PayoutType');
    }

}
