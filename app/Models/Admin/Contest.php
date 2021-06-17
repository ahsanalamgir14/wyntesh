<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Validator, Carbon\Carbon;

class Contest extends Model {

	protected $table = 'contests';
	public $timestamps = true;

    protected $appends = ['is_ended'];

    protected $casts = [
        'created_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function getIsEndedAttribute()
    {
        $today=Carbon::today();

        if($today->gt($this->end_date)){
            return true;    
        }
    }

    public function contest_members()
    {
        return $this->hasMany('App\Models\Admin\ContestMember');
    }

}