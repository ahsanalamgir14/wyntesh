<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class PayoutType extends Model
{
    protected $table = 'payout_types';
    public $timestamps = true;

    public function income()
    {
        return $this->belongsToMany('App\Models\Admin\Income', 'payout_type_incomes', 'income_id', 'payout_type_id');
    }

}
