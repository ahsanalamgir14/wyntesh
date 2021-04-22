<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $table = 'payouts';
    public $timestamps = true;

    protected $casts = [
        'created_at' => 'datetime',
        'sales_start_date' => 'datetime',
        'sales_end_date' => 'datetime',
    ];

    public function payout_type()
    {
        return $this->belongsTo('App\Models\Admin\PayoutType','payout_type_id');
    }

    public function incomes()
    {
        return $this->hasMany('App\Models\Admin\PayoutIncome');
    }

}
