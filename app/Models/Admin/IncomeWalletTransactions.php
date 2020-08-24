<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomeWalletTransactions extends Model
{
    //


    protected $table = 'income_wallet_transactions';
    public $timestamps = true;
    use SoftDeletes;


   public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member')->with('user');
    }

    public function transfered_from_user()
    {
        return $this->belongsTo('App\Models\User\User','transfered_from');
    }

    public function transfered_to_user()
    {
        return $this->belongsTo('App\Models\User\User','transfered_to');
    }

    public function transaction_by_user()
    {
        return $this->belongsTo('App\Models\User\User','transaction_by');
    }

    public function transaction()
    {
        return $this->belongsTo('App\Models\Superadmin\TransactionType','transaction_type_id');
    }


}
