<?php

namespace App\Models\Admin;

use App\Models\User\User;
use App\Http\Controllers\User\MembersController;
use Illuminate\Database\Eloquent\Model;
use DB;
use App\Models\Admin\MembersLegPv;
use App\Models\Admin\MemberCarryForwardPv;

class GMember extends Model
{
    protected $table = 'members';
    public $timestamps = true;
    protected $appends = ['group_pv','team_size','leg_pv','total_leg_pv','carry_forward'];
    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }

    public function rank()
    {
        return $this->belongsTo('App\Models\Admin\Rank','rank_id');
    }

    public function kyc()
    {
        return $this->hasOne('App\Models\User\Kyc','member_id');
    }
   

    public function leg()
    {
        return $this->hasMany('App\Models\Admin\MembersLegPv','member_id');
    }

    public function getGroupPvAttribute()
    {
        $group_pv=0;
        $legs=$this->leg;
        foreach ($legs as $leg) {
            $group_pv+=$leg->pv;
        }
        return $group_pv;
    }

    public function getLegPvAttribute()
    {
        $last_payout=Payout::latest()->first();
        $legs= MembersLegPv::addSelect(['*', \DB::raw('sum(pv) as totalPv')])
                    ->whereDate('created_at','>',$last_payout->sales_end_date)
                    ->where('member_id',$this->id)
                    ->orderBy('totalPv','desc')
                    ->groupBy('position')
                    ->get()->pluck('totalPv','position')->toArray();

        $last_carry_forward=MemberCarryForwardPv::where('member_id',$this->id)->orderBy('payout_id','desc')->first();

        if($last_carry_forward){
                $exsting_pv=intval(isset($legs[$last_carry_forward->position])?$legs[$last_carry_forward->position]:0);
                $legs[$last_carry_forward->position]=$exsting_pv+$last_carry_forward->pv;
        }
        

        return $legs;
    }

    public function getTotalLegPvAttribute()
    {
        $legs= MembersLegPv::addSelect(['*', \DB::raw('sum(pv) as totalPv')])
                    ->where('member_id',$this->id)
                    ->orderBy('totalPv','desc')
                    ->groupBy('position')
                    ->get()->pluck('totalPv','position')->toArray();

        return $legs;
    }

    public function getCarryForwardAttribute()
    {
        
        $last_carry_forward=MemberCarryForwardPv::where('member_id',$this->id)->orderBy('payout_id','desc')->first();

        return $last_carry_forward;
    }

    public function getTeamSizeAttribute()
    {
        $MembersController=new MembersController;
        $childs=$MembersController->getChildsOfParent($this->id);
        if($childs){
            return count($childs);
        }else{
            return 0;
        }
    }

    public function parent()
    {
        return $this->belongsTo(self::class,'parent_id')->with('user:id,username,name,is_active');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->with('user:id,username,name,is_active')->with('kyc:id,member_id,verification_status,is_verified')->with('leg')->with('rank')
            ->withCount(['leg as group_pv' => function($query){
               $query->select(DB::raw('sum(pv)'));
            }]);
    }

    public function sponsored()
    {
        return $this->hasMany(self::class, 'sponsor_id')->with('user:id,username,name,is_active')->with('kyc:id,member_id,verification_status,is_verified');
    }

    public function sponsor()
    {
        return $this->belongsTo(self::class,'sponsor_id')->with('user:id,username,name,is_active');
    }
}
