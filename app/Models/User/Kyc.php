<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Kyc extends Model
{
    
	protected $table = 'kyc';

    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }
}
