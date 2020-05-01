<?php

namespace App\Models\Superadmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMode extends Model
{
	protected $table = 'payment_modes';
    public $timestamps = true;
    use SoftDeletes;

}
