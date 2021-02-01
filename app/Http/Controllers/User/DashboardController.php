<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User\User;
use App\Models\Admin\Member;
use App\Models\Admin\MemberPayout;
use App\Models\Admin\MembersLegPv;
use App\Models\Admin\MemberPayoutIncome;
use App\Models\User\Order;
use App\Models\Admin\Sale;
use App\Models\Admin\Income;
use App\Models\Admin\Pin;
use App\Models\Admin\Withdrawal;
use App\Models\Admin\WalletTransaction;
use App\Models\Admin\CompanySetting;
use App\Http\Controllers\User\MembersController;
use App\Models\Superadmin\TransactionType;
use App\Models\Admin\AffiliateBonus;

use App\Models\Admin\Reward;
use JWTAuth;
use Carbon\Carbon;
use DB;
class DashboardController extends Controller
{
    
    public function stats(){
    	$User=JWTAuth::user();

      
        $Member=User::with('kyc')
                ->with('member:id,user_id,wallet_balance')
                ->with('member.rank')
                ->find($User->id);
    	
        $MembersController=new MembersController;
        $total_group_bv=MembersLegPv::where('member_id',$User->member->id)->sum('pv');
        $total_matched=$User->member->total_matched_bv;
    	$downlines=count($MembersController->getChildsOfParent($User->member->id));
        $referrals=$User->member->sponsored->count();
    	$total_purchase= floor(Order::where('user_id',$User->id)->whereNotIn('delivery_status',['Order Cancelled','Order Returned'])->sum('net_amount'));
        $distributor_discount= floor(Order::where('user_id',$User->id)->whereNotIn('delivery_status',['Order Cancelled','Order Returned'])->sum('distributor_discount'));
    	$withdrawals=floor(Withdrawal::where('member_id',$User->member->id)->sum('amount'));
        $pins_available=Pin::where('used_at',null)->where('owned_by',$User->member->id)->count();
        $current_personal_pv=$User->member->current_personal_pv;
        $total_personal_pv=$User->member->total_personal_pv;
        $balance=floatval($User->member->wallet_balance);
      
        $affiliate_bonus=AffiliateBonus::where('member_id',$User->member->id)->sum('amount');        
        $reward=Reward::where('member_id',$User->member->id)->sum('amount');

        $total_payout=MemberPayout::where('member_id',$User->member->id)->sum('payout_amount');
        $cur_affiliate_bonus=AffiliateBonus::where('member_id',$User->member->id)->whereMonth('created_at',date('m'))->sum('amount');        
        $cur_reward=Reward::where('member_id',$User->member->id)->whereMonth('created_at',date('m'))->sum('amount');
        $total_payout+=$cur_reward+$cur_affiliate_bonus;

        $income_wallet_balance=Member::where('id',$User->member->id)->sum('income_wallet_balance');

        $luxury_wallet_balance=Member::where('id',$User->member->id)->sum('luxury_wallet_balance');
        
        $squad_bonus_income=Income::where('code','SQUAD')->first();
        $squad_bonus = MemberPayoutIncome::where('income_id',$squad_bonus_income->id)->where('member_id',$User->member->id)->sum(\DB::raw('payout_amount'));

        $elevation_income=Income::where('code','ELEVATION')->first();
        $elevation = MemberPayoutIncome::where('income_id',$elevation_income->id)->where('member_id',$User->member->id)->sum(\DB::raw('payout_amount'));

        $luxury_income=Income::where('code','LUXURY')->first();
        $luxury = MemberPayoutIncome::where('income_id',$luxury_income->id)->where('member_id',$User->member->id)->sum(\DB::raw('payout_amount'));

        $premium_income=Income::where('code','PREMIUM')->first();
        $premium = MemberPayoutIncome::where('income_id',$premium_income->id)->where('member_id',$User->member->id)->sum(\DB::raw('payout_amount'));

        $self_pv=Member::where('id',$User->member->id)->sum('total_personal_pv');
        $current_personal_pv=Member::where('id',$User->member->id)->sum('current_personal_pv');

        $response = array(
            'status' => true,
            'message'=>'Stats recieved',
            'stats'=>array(
                'current_personal_pv'=>$current_personal_pv,
                'self_pv'=>$self_pv,
                'premium'=>$premium,
                'luxury'=>$luxury,
                'elevation'=>$elevation,
                'squad_bonus'=>$squad_bonus,
                'downlines'=>$downlines,
                'referrals'=>$referrals,
                'total_purchase'=>$total_purchase,
                'withdrawals'=>$withdrawals,
                'pins_available'=>$pins_available,
                'balance'=>$balance,
                'total_payout'=>$total_payout,
                'income_wallet_balance'=>$income_wallet_balance,
                'total_reward'=>$reward,
                'current_personal_pv'=>$current_personal_pv,
                'total_personal_pv'=>$total_personal_pv,
                'member'=>$Member,
                'total_group_bv'=>$total_group_bv,
                'total_matched'=>$total_matched,
                'distributor_discount'=>$distributor_discount,
                'affiliateIncome'=>$affiliate_bonus,
                'luxury_wallet_balance'=>$luxury_wallet_balance,
                // 'cashback_income'=>$cashback_income,
            )
        );             
        return response()->json($response, 200);

    }

    public function orderStats(){
        $from=Carbon::now()->subDays(7)->format('Y-m-d');
        $to=Carbon::now()->format('Y-m-d'); 
        $User=JWTAuth::user();
        $MembersController=new MembersController;
        $downlines=$MembersController->getChildsOfParent($User->member->id);        

        $sales=Sale::whereBetween('created_at', [$from,$to])
                    ->whereIn('member_id',$downlines)
                    ->groupBy('date')
                    ->orderBy('date', 'ASC')
                    ->get(array(
                        DB::raw('Date(created_at) as date'),
                        DB::raw('sum(pv) as sum')
                    ));
        $od=[];

        for ($i=7; $i >= 1 ; $i--) {
            if(count($sales)){
                foreach ($sales as $sale) {
                    if($sale->date==Carbon::now()->subDays($i)->format('Y-m-d') ){
                        $od[$i]['date']=Carbon::parse($sale->date)->format('m-d');
                        $od[$i]['sum']=floor($sale->sum);
                    }else{
                        if(!isset($od[$i])){
                            $od[$i]['date']=Carbon::now()->subDays($i)->format('m-d');
                            $od[$i]['sum']=0;
                        }
                        
                    }
                 }
            }else{
                $od[$i]['date']=Carbon::now()->subDays($i)->format('m-d');
                $od[$i]['sum']=0;
            }           
        }
        $sale=[];
        
        foreach ($od as $o) {
            $sale[]=$o;
        }
        
        $response = array('status' => true,'message'=>'Stats recieved','sales'=>$sale);             
        return response()->json($response, 200);
    }

    public function downlineStats(){
    	$User=JWTAuth::user();
    	$from=Carbon::now()->subDays(7)->format('Y-m-d');
        $to=Carbon::now()->format('Y-m-d');
        $MembersController=new MembersController; 
        $downlineIds=$MembersController->getChildsOfParent($User->member->id);
    	$mydownlines=Member::whereBetween('created_at', [$from,$to])
    				->whereIn('id',$downlineIds)
    				->groupBy('date')
                    ->orderBy('date', 'ASC')
                    ->get(array(
                        DB::raw('Date(created_at) as date'),
                        DB::raw('count(*) as count')
                    ));

        $ar=[];

        for ($i=7; $i >= 1 ; $i--) {

        	if(count($mydownlines)){
        		foreach ($mydownlines as $downline) {
	        	 	if($downline->date==Carbon::now()->subDays($i)->format('Y-m-d') ){
	        	 		$ar[$i]['date']=Carbon::parse($downline->date)->format('m-d');
	        	 		$ar[$i]['count']=floor($downline->count);
	        		}else{
	        			if(!isset($ar[$i])){
	        				$ar[$i]['date']=Carbon::now()->subDays($i)->format('m-d');
	        				$ar[$i]['count']=0;
	        			}
	        			
	        		}
	        	}	
        	}else{
        		$ar[$i]['date']=Carbon::now()->subDays($i)->format('m-d');
	        	$ar[$i]['count']=0;
        	}
        	         	
        }

        $downlines=[];
        foreach ($ar as $o) {
        	$downlines[]=$o;
        }

        $response = array('status' => true,'message'=>'Stats recieved','downlines'=>$downlines);             
        return response()->json($response, 200);
    }

    public function referralStats(){
        $User=JWTAuth::user();
        $from=Carbon::now()->subDays(7)->format('Y-m-d');
        $to=Carbon::now()->format('Y-m-d');
        $MembersController=new MembersController; 
        $mydownlines=Member::whereBetween('created_at', [$from,$to])
                    ->where('sponsor_id',$User->member_id)
                    ->groupBy('date')
                    ->orderBy('date', 'ASC')
                    ->get(array(
                        DB::raw('Date(created_at) as date'),
                        DB::raw('count(*) as count')
                    ));

        $ar=[];

        for ($i=7; $i >= 1 ; $i--) {

            if(count($mydownlines)){
                foreach ($mydownlines as $downline) {
                    if($downline->date==Carbon::now()->subDays($i)->format('Y-m-d') ){
                        $ar[$i]['date']=Carbon::parse($downline->date)->format('m-d');
                        $ar[$i]['count']=floor($downline->count);
                    }else{
                        if(!isset($ar[$i])){
                            $ar[$i]['date']=Carbon::now()->subDays($i)->format('m-d');
                            $ar[$i]['count']=0;
                        }
                        
                    }
                }   
            }else{
                $ar[$i]['date']=Carbon::now()->subDays($i)->format('m-d');
                $ar[$i]['count']=0;
            }
                        
        }

        $downlines=[];
        foreach ($ar as $o) {
            $downlines[]=$o;
        }

        $response = array('status' => true,'message'=>'Stats recieved','referrals'=>$downlines);             
        return response()->json($response, 200);
    }

    public function payoutStats(){
        $User=JWTAuth::user();
        $from   = Carbon::now()->modify('-7 months')->firstOfMonth()->toDateString('Y-m-d');        
        $to     = Carbon::now()->modify('-1 months')->endOfMonth()->toDateString('Y-m-d');

        $od=[];

        $reward = Reward::whereBetween('created_at', [$from,$to])->where('member_id',$User->member->id)
        ->select(DB::raw('sum(amount) as income'),DB::raw("DATE_FORMAT(created_at, '%m-%Y') new_date"),  DB::raw('YEAR(created_at) year, MONTH(created_at) month'),DB::raw('Date(created_at) as date'))
        ->groupBy('year','month')
        ->orderBy('created_at','ASC')
        ->get();


        $affiliate_bonus = AffiliateBonus::whereBetween('created_at', [$from,$to])->where('member_id',$User->member->id)
        ->select(DB::raw('sum(amount) as income'),DB::raw("DATE_FORMAT(created_at, '%m-%Y') new_date"),  DB::raw('YEAR(created_at) year, MONTH(created_at) month'),DB::raw('Date(created_at) as date'))
        ->groupBy('year','month')
        ->orderBy('created_at','ASC')
        ->get();

        $payouts = MemberPayout::whereBetween('created_at', [$from,$to])->where('member_id',$User->member->id)
        ->select(DB::raw('sum(payout_amount) as income'),DB::raw("DATE_FORMAT(created_at, '%m-%Y') new_date"),  DB::raw('YEAR(created_at) year, MONTH(created_at) month'),DB::raw('Date(created_at) as date'))
        ->groupBy('year','month')
        ->orderBy('created_at','ASC')
        ->get();

        for ($i=7; $i >= 1 ; $i--) {
            if(count($payouts)){
                foreach ($payouts as $key=>$payout) {
                    $date_to_compare=Carbon::parse($payout->date)->format('Y-m');

                    if($date_to_compare == Carbon::now()->modify('-'.$i.' months')->format('Y-m') ){
                        $od[$i]['date']=$date_to_compare;
                        $od[$i]['income']=floor($payout->income);
                    }else{
                        if(!isset($od[$i])){
                            $od[$i]['date']=Carbon::now()->modify('-'.$i.' months')->format('Y-m');
                            $od[$i]['income']=0;
                        }
                    }
                }   
            }else{
                $od[$i]['date']=Carbon::now()->modify('-'.$i.' months')->format('Y-m') ;
                $od[$i]['income']=0;
            }
        }

       
        $payout_list=[];
        foreach ($od as $o) {
            $payout_list[]=$o;
        }
        $response = array('status' => true,'message'=>'Stats recieved','payouts'=>$payout_list);             
        return response()->json($response, 200);
    }

    public function latestDownlines(){
    	$User=JWTAuth::user();
    	$MembersController=new MembersController;
    	$downlineIds=$MembersController->getChildsOfParent($User->member->id);

    	$downlines=Member::
    				select('id','user_id')
    				->whereIn('id',$downlineIds)
    				->with('user:id,username,name,created_at')
                    ->orderBy('id', 'DESC')->limit(5)->get();

        $response = array('status' => true,'message'=>'Latest downlines recieved','data'=>$downlines);             
        return response()->json($response, 200);
    }

    public function latestTransactions(){
    	$User=JWTAuth::user();
    	
    	$WalletTransactions=WalletTransaction::select();           
        $WalletTransactions=$WalletTransactions->with('transaction_by_user','transfered_from_user','transfered_to_user','transaction')->where('member_id',$User->member->id)->limit(5)->get();
        
        $response = array('status' => true,'message'=>'Latest transactions recieved','data'=>$WalletTransactions);             
        return response()->json($response, 200);
    }
}
