<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PinTransferLog extends Model
{
    protected $table = 'pin_transfer_logs';
    public $timestamps = true;
    use SoftDeletes;


    public function transfered_from()
    {
        return $this->belongsTo('App\Models\User\User','transfered_from');
    }

    public function transfered_to()
    {
        return $this->belongsTo('App\Models\User\User','transfered_to');
    }

    public function transfered_by()
    {
        return $this->belongsTo('App\Models\User\User','transfered_by');
    }

    public function pin()
    {
        return $this->belongsTo('App\Models\Admin\Pin','pin_id');
    }


}
