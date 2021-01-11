<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
	protected $table = 'currencies';
    public $timestamps = true;
    use SoftDeletes;

    protected $fillable=['name','code','symbol','conversion_rate'];
}
