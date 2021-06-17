<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Validator;

class ContestBanner extends Model {

	protected $table = 'contest_banners';
	public $timestamps = true;

    public function contest()
    {
        return $this->belongsTo('App\Models\Admin\Contest');
    }

    public function rank()
    {
        return $this->belongsTo('App\Models\Admin\Rank');
    }

}