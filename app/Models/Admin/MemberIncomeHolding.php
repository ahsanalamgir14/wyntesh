<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberIncomeHolding extends Model
{
    protected $table = 'member_income_holdings';
    public $timestamps = true;
    use SoftDeletes;

    public function payout()
    {
        return $this->belongsTo('App\Models\Admin\Payout');
    }

    public function income()
    {
        return $this->belongsTo('App\Models\Admin\Income');
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
