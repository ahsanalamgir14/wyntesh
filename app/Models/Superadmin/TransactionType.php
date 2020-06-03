<?php

namespace App\Models\Superadmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionType extends Model
{
	protected $table = 'transaction_types';
    public $timestamps = true;
    use SoftDeletes;

}
