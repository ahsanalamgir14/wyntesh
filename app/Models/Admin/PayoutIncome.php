<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class PayoutIncome extends Model
{
    protected $table = 'payout_incomes';
    public $timestamps = true;

    public function income()
    {
        return $this->belongsTo('App\Models\Admin\Income');
    }

    public function payout()
    {
        return $this->belongsTo('App\Models\Admin\Payout');
    }

}
