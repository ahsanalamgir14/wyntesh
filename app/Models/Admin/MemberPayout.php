<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\RankLog;
use App\Models\Admin\AffiliateBonus;
use Carbon\Carbon;
class MemberPayout extends Model
{
    protected $table = 'member_payouts';
    public $timestamps = true;
    protected $appends = ['rank','legs','affiliate_tds','affiliate_income'];

    public function payout()
    {
        return $this->belongsTo('App\Models\Admin\Payout','payout_id');
    }    

    public function getAffiliateTdsAttribute()
    {
        $affiliate_tds=0;
        if($this->payout){
          $affiliate_tds=AffiliateBonus::whereDate('created_at','>=',$this->payout->sales_start_date)->whereDate('created_at','<=',$this->payout->sales_end_date)->where('member_id',$this->member_id)->sum('tds_amount');
        }
        return $affiliate_tds;
    }

    public function getAffiliateIncomeAttribute()
    {
        $affiliate_income=0;
        if($this->payout){
          $affiliate_income=AffiliateBonus::whereDate('created_at','>=',$this->payout->sales_start_date)->whereDate('created_at','<=',$this->payout->sales_end_date)->where('member_id',$this->member_id)->sum('amount');
        }
        return $affiliate_income;
    }

   
    public function getRankAttribute()
    {
        $Rank=RankLog::where('payout_id',$this->payout_id)->where('member_id',$this->member_id)->latest()->first();
        if($Rank){
            return $Rank->rank;    
        }else{
            return '';
        }
        

    }

    public function getLegsAttribute()
    {
        if($this->payout){
            $legs= MembersLegPv::addSelect(['*', \DB::raw('sum(pv) as totalPv')])
           ->whereDate('created_at','<=',$this->payout->sales_end_date)
           ->whereDate('created_at','>=',$this->payout->sales_start_date)
           ->where('member_id',$this->member->id)
           ->orderBy('totalPv','desc')
           ->groupBy('position')
           ->get();
           return $legs; 
        }else{
            return '';
        }    
    }

    public function member()
    {
        return $this->belongsTo('App\Models\Admin\Member');
    }  

    public function scopeWhereRank($query, $relation, $date, $minimum_rank) {
        $query->whereHas(
            $relation,
            function ($query) use ($date,$minimum_rank) {
                $query->where('rank_id',$minimum_rank)->whereDate('created_at', '<=', $date);
            }
        );
    }

}
