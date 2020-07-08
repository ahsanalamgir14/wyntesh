<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class RankLog extends Model {


	protected $table = 'rank_logs';
	public $timestamps = true;

	 public function payout()
    {
        return $this->belongsTo('App\Models\Admin\Payout');
    }

    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member');
    }

    public function rank()
    {
        return $this->belongsTo('App\Models\Admin\Rank');
    }

}