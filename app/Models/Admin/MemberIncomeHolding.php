<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class MemberIncomeHolding extends Model
{
    protected $table = 'member_income_holdings';
    public $timestamps = true;

    public function payout()
    {
        return $this->belongsTo('App\Models\Admin\Payout');
    }

    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member');
    }

    public function rank()
    {
        return $this->belongsTo('App\Models\Admin\Rank','rank_id');
    }
}
