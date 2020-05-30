<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $table = 'incomes';
    public $timestamps = true;

    public function income_parameters()
    {
        return $this->hasMany('App\Models\Admin\IncomeParameter');
    }

    public function payout_type()
    {
        return $this->belongsToMany('App\Models\Admin\PayoutType', 'payout_type_incomes', 'income_id', 'payout_type_id');
    }

}
