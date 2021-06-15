<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Validator;

class ContestReward extends Model {

	protected $table = 'contest_rewards';
	public $timestamps = true;

	public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member');
    }

    public function contest()
    {
        return $this->belongsTo('App\Models\Admin\Contest');
    }

}