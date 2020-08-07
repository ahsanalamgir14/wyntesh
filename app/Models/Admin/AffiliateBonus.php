<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AffiliateBonus extends Model
{
    protected $table = 'affiliate_bonus';
    public $timestamps = true;
    use SoftDeletes;


    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member')->with('user');
    }

    public function order()
    {
        return $this->belongsTo('App\Models\User\Order');
    }

}
