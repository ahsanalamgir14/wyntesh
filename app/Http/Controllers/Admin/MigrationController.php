<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

use App\Models\User\User;
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
use App\Models\Admin\LuxuryWalletTransaction;
use App\Models\Admin\Reward;
use App\Models\User\Order;
use Illuminate\Support\Facades\Storage;

class MigrationController extends Controller
{    

    public function doMigration(){
        //$this->affiliateCorrection();
        $this->correctLuxuryWallet();
       
    }

    public function correctLuxuryWallet(){
        $LuxuryWalletTransactions=LuxuryWalletTransaction::get();

        foreach ($LuxuryWalletTransactions as $tran) {
            $user=User::where('id',$tran->member_id)->first();
            $tran->member_id=$user->member->id;
            $tran->save();
        }
    }

    public function affiliateCorrection(){
        $payout=Payout::find(20);

        $payout_month=Carbon::createFromFormat('Y-m-d', $payout->sales_start_date)->format('m');
        $payout_year=Carbon::createFromFormat('Y-m-d', $payout->sales_start_date)->format('Y');
            
        $Members=Member::all();

        foreach ($Members as $Member) {
            
            $MemberPayoutIncomesReward=MemberPayoutIncome::where('payout_id',$payout->id)->where('income_id',1)->where('member_id',$Member->id)->first();

            $Reward= Reward::select([                
                DB::raw("SUM(amount) as total_payout_amount"),
                DB::raw("SUM(tds_amount) as total_tds"),
                DB::raw("SUM(final_amount) as total_net_payable_amount"),
                DB::raw("tds_percent"),
            ])
            ->whereMonth('created_at',$payout_month)
            ->whereYear('created_at',$payout_year)
            ->where('member_id',$Member->id)
            ->first();

            if(!$MemberPayoutIncomesReward){
                $MemberPayout=MemberPayout::where('payout_id',$payout->id)->where('member_id',$Member->id)->first();

                if(!$MemberPayout)
                    continue;

                $MemberPayoutIncomesReward=new MemberPayoutIncome;
                $MemberPayoutIncomesReward->member_payout_id=$MemberPayout->id; 
                $MemberPayoutIncomesReward->member_id=$Member->id; 
                $MemberPayoutIncomesReward->payout_id=$payout->id; 
            }

            if($Reward->total_payout_amount){
                 $MemberPayoutIncomesReward->payout_amount                   = $Reward->total_payout_amount;
                $MemberPayoutIncomesReward->tds                             = $Reward->total_tds;        
                $MemberPayoutIncomesReward->tds_percent                     = $Reward->tds_percent;
                $MemberPayoutIncomesReward->net_payable_amount              = $Reward->total_net_payable_amount;     
                $MemberPayoutIncomesReward->save();
            }

            $MemberPayoutIncomeAffiliate=MemberPayoutIncome::where('payout_id',$payout->id)->where('member_id',$Member->id)->where('income_id',2)->first();

            $AffiliateBonus= AffiliateBonus::select([                
                DB::raw("SUM(amount) as total_payout_amount"),
                DB::raw("SUM(tds_amount) as total_tds"),
                DB::raw("SUM(final_amount) as total_net_payable_amount"),
                DB::raw("tds_percent"),
            ])
            ->whereMonth('created_at',$payout_month)
            ->whereYear('created_at',$payout_year)
            ->where('member_id',$Member->id)
            ->first();

            if(!$MemberPayoutIncomeAffiliate){
                $MemberPayoutIncomeAffiliate=new MemberPayoutIncome;
                $MemberPayoutIncomeAffiliate->member_payout_id=$MemberPayout->id; 
                $MemberPayoutIncomeAffiliate->member_id=$Member->id; 
                $MemberPayoutIncomeAffiliate->payout_id=$payout->id; 
            }

            if($AffiliateBonus->total_payout_amount){

                $MemberPayoutIncomeAffiliate->payout_amount                   = $AffiliateBonus->total_payout_amount;
                $MemberPayoutIncomeAffiliate->tds                             = $AffiliateBonus->total_tds;        
                $MemberPayoutIncomeAffiliate->tds_percent                     = $AffiliateBonus->tds_percent;
                $MemberPayoutIncomeAffiliate->net_payable_amount              = $AffiliateBonus->total_net_payable_amount;     
                $MemberPayoutIncomeAffiliate->save();
            }

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
