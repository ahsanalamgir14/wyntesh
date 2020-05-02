<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PinRequest extends Model
{
    protected $table = 'pin_requests';
    public $timestamps = true;
    use SoftDeletes;

    public function package()
    {
        return $this->belongsTo('App\Models\Admin\Package');
    }

    public function payment_mode()
    {
        return $this->belongsTo('App\Models\Superadmin\PaymentMode','payment_mode');
    }

    public function bank()
    {
        return $this->belongsTo('App\Models\Admin\BankPartner','bank_id');
    }

    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member','requested_by')->with('user:id,name,username');
    }

    public function approver()
    {
        return $this->belongsTo('App\Models\User\User','approved_by');
    }



}
