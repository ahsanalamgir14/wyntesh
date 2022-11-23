<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MatchingPoint extends Model
{
    protected $table = 'matching_points';
    public $timestamps = true;


    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member')->with('user');
    }

    public function scopeWhereRank($query, $relation, $rank_id, $date) {
        $query->whereHas(
            $relation,
            function ($query) use ($rank_id, $date) {
                $query->where('rank_id',$rank_id)->whereDate('created_at', '<=', $date);
            }
        );
    }

}
