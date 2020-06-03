<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WithdrawalRequest extends Model
{
    protected $table = 'withdrawal_requests';
    public $timestamps = true;
    use SoftDeletes;

    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member','member_id')->with('user:id,name,username');
    }

    public function approver()
    {
        return $this->belongsTo('App\Models\User\User','approved_by');
    }



}
