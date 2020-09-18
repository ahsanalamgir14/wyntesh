<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class WallOfWyntash extends Model
{
    
    protected $table = 'wall_of_wyntash';
    public function user()
    {
        return $this->belongsTo('App\Models\User\User','username','username');
    }

}
