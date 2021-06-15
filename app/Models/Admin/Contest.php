<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Validator;

class Contest extends Model {

	protected $table = 'contests';
	public $timestamps = true;

    protected $casts = [
        'created_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function contest_members()
    {
        return $this->hasMany('App\Models\Admin\ContestMember');
    }

}