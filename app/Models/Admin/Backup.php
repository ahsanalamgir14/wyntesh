<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Backup extends Model
{
    public $timestamps = true;
	use SoftDeletes;

}
