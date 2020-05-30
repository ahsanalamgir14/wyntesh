<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Rank extends Model {


	protected $table = 'ranks';
	public $timestamps = true;

    public function legRank()
    {
        return $this->belongsTo(self::class,'leg_rank');
    }

}