<?php

namespace App\Models\Superadmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Status extends Model
{
	protected $table = 'statuses';
    public $timestamps = true;
    use SoftDeletes;

}
