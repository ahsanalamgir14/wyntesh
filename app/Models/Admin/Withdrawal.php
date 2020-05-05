<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Withdrawal extends Model
{
    protected $table = 'withdrawals';
    public $timestamps = true;
    use SoftDeletes;

    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member')->with('user');
    }

    public function transaction_by()
    {
        return $this->belongsTo('App\Models\User\User','transaction_by');
    }

    public function request()
    {
        return $this->belongsTo('App\Models\Admin\WithdrawalRequest');
    }


}
