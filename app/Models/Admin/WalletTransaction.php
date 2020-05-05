<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletTransaction extends Model
{
    protected $table = 'wallet_transactions';
    public $timestamps = true;
    use SoftDeletes;


    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member')->with('user');
    }

    public function transfered_from()
    {
        return $this->belongsTo('App\Models\User\User','transfered_from');
    }

    public function transfered_to()
    {
        return $this->belongsTo('App\Models\User\User','transfered_to');
    }

    public function transaction_by()
    {
        return $this->belongsTo('App\Models\User\User','transaction_by');
    }

    public function transaction()
    {
        return $this->belongsTo('App\Models\Admin\TransactionType','transaction_type_id');
    }


}
