<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

use App\Models\Admin\User;
use App\Models\Admin\Member;
use App\Models\Admin\Product;
use App\Models\Admin\Payout;
use App\Models\Admin\MemberPayoutIncome;
use App\Models\Admin\PayoutIncome;
use App\Models\Admin\MemberPayout;
use App\Models\Admin\MemberIncomeHolding;
use App\Models\Admin\ProductImage;
use App\Models\Admin\ProductVariant;
use App\Models\Admin\AffiliateBonus;
use App\Models\Admin\Reward;
use App\Models\User\Order;
use Illuminate\Support\Facades\Storage;

class MigrationController extends Controller
{    

    public function doMigration(){
        $this->affiliateCorrection();
       
    }

    public function affiliateCorrection(){
        $payout=Payout::find(20);

       

        $payout_month=Carbon::createFromFormat('Y-m-d', $payout->sales_start_date)->format('m');
        $payout_year=Carbon::createFromFormat('Y-m-d', $payout->sales_start_date)->format('Y');
        
        $MemberPayoutIncomesReward=MemberPayoutIncome::where('payout_id',$payout->id)->where('income_id',1)->get();
         
        foreach ($MemberPayoutIncomesReward as $MemberPayoutIncome) {
             $Reward= Reward::select([                
                DB::raw("SUM(amount) as total_payout_amount"),
                DB::raw("SUM(tds_amount) as total_tds"),
                DB::raw("SUM(final_amount) as total_net_payable_amount"),
                DB::raw("tds_percent"),
            ])
            ->whereMonth('created_at',$payout_month)
            ->whereYear('created_at',$payout_year)
            ->where('member_id',$MemberPayoutIncome->member_id)
            ->first();

            $MemberPayoutIncome->payout_amount                   = $Reward->total_payout_amount;
            $MemberPayoutIncome->tds                             = $Reward->total_tds;        
            $MemberPayoutIncome->tds_percent                     = $Reward->tds_percent;
            $MemberPayoutIncome->net_payable_amount              = $Reward->total_net_payable_amount;     
            $MemberPayoutIncome->save();

        }

       
         $MemberPayoutIncomes=MemberPayoutIncome::where('payout_id',$payout->id)->where('income_id',2)->get();

        foreach ($MemberPayoutIncomes as $MemberPayoutIncome) {
             $AffiliateBonus= AffiliateBonus::select([                
                DB::raw("SUM(amount) as total_payout_amount"),
                DB::raw("SUM(tds_amount) as total_tds"),
                DB::raw("SUM(final_amount) as total_net_payable_amount"),
                DB::raw("tds_percent"),
            ])
            ->whereMonth('created_at',$payout_month)
            ->whereYear('created_at',$payout_year)
            ->where('member_id',$MemberPayoutIncome->member_id)
            ->first();

            $MemberPayoutIncome->payout_amount                   = $AffiliateBonus->total_payout_amount;
            $MemberPayoutIncome->tds                             = $AffiliateBonus->total_tds;        
            $MemberPayoutIncome->tds_percent                     = $AffiliateBonus->tds_percent;
            $MemberPayoutIncome->net_payable_amount              = $AffiliateBonus->total_net_payable_amount;     
            $MemberPayoutIncome->save();

        }
        
        $this->updateMemberPayoutSum($payout);
        $this->updatePayoutSum($payout);
        
    }

    public function updateMemberPayoutSum($payout){
        $Members=Member::whereHas('user',function($q){
            $q->where('is_active',1);
            $q->where('is_blocked',0);
        })->orderBy('level','desc')->get();

        foreach ($Members as $Member) {
            $MemberPayout=MemberPayout::where('member_id',$Member->id)->where('payout_id',$payout->id)->first();

            if($MemberPayout){
                $total_member_tds=MemberPayoutIncome::where('payout_id',$MemberPayout->payout_id)->where('member_id',$MemberPayout->member_id)->sum('tds');

                $total_member_admin_fee=MemberPayoutIncome::where('payout_id',$MemberPayout->payout_id)->where('member_id',$MemberPayout->member_id)->sum('admin_fee');

                $total_member_payout_amount=MemberPayoutIncome::where('payout_id',$MemberPayout->payout_id)->where('member_id',$MemberPayout->member_id)->sum('payout_amount');

                $total_net_payable_amount=MemberPayoutIncome::where('payout_id',$MemberPayout->payout_id)->where('member_id',$MemberPayout->member_id)->sum('net_payable_amount');

                $MemberPayout->payout_amount=$total_member_payout_amount;
                $MemberPayout->tds=$total_member_tds;
                $MemberPayout->admin_fee=$total_member_admin_fee;
                $MemberPayout->net_payable_amount=$total_net_payable_amount;
                $MemberPayout->save();
                $MemberPayout->member->current_personal_pv=0;
                $MemberPayout->member->save();

            }
            
        }        
        
    }

    public function updatePayoutSum($payout){        
        $PayoutIncomes=PayoutIncome::where('payout_id',$payout->id)->get();
        // Calculating income wise total payout.
        foreach ($PayoutIncomes as $PayoutIncome) {
            $IncomePayout= MemberPayoutIncome::select([                
                DB::raw("SUM(payout_amount) as total_payout_amount"),
                DB::raw("SUM(tds) as total_tds"),
                DB::raw("SUM(admin_fee) as total_admin_fee"),
                DB::raw("SUM(net_payable_amount) as total_net_payable_amount"),
            ])->where('payout_id',$PayoutIncome->payout_id)->where('income_id',$PayoutIncome->income_id)->groupBy('payout_id')->first();

            if($IncomePayout){                
                $PayoutIncome->payout_amount=$IncomePayout->total_payout_amount;
                $PayoutIncome->tds=$IncomePayout->total_tds;
                $PayoutIncome->admin_fee=$IncomePayout->total_admin_fee;
                
                $PayoutIncome->net_payable_amount=$IncomePayout->total_net_payable_amount;
                $PayoutIncome->save();
            }

        }

        $TotalPayout=MemberPayoutIncome::select([                
            DB::raw("SUM(payout_amount) as total_payout_amount"),
            DB::raw("SUM(tds) as total_tds"),
            DB::raw("SUM(admin_fee) as total_admin_fee"),
            DB::raw("SUM(net_payable_amount) as total_net_payable_amount"),
        ])->where('payout_id',$payout->id)->groupBy('payout_id')->first();

        if($TotalPayout){            
            $payout->payout_amount=$TotalPayout->total_payout_amount;
            $payout->tds=$TotalPayout->total_tds;
            $payout->admin_fee=$TotalPayout->total_admin_fee;
            $payout->net_payable_amount=$TotalPayout->total_net_payable_amount;
        }
        $payout->save();
    }

    
}
