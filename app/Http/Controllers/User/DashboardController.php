<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User\User;
use App\Models\Admin\Member;
use App\Models\Admin\MemberPayout;
use App\Models\Admin\MemberMonthlyLegPv;
use App\Models\User\Order;
use App\Models\Admin\Sale;
use App\Models\Admin\Pin;
use App\Models\Admin\Withdrawal;
use App\Models\Admin\WalletTransaction;
use App\Http\Controllers\User\MembersController;
use App\Models\Superadmin\TransactionType;
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
        $total_group_bv=MemberMonthlyLegPv::where('member_id',$User->member->id)->sum('pv');
        $total_matched=$User->member->total_matched_bv;
    	$downlines=count($MembersController->getChildsOfParent($User->member->id));
        $referrals=$User->member->sponsored->count();
    	$total_purchase= floor(Order::where('user_id',$User->id)->whereNotIn('delivery_status',['Order Cancelled','Order Returned'])->sum('final_amount'));
        $distributor_discount= floor(Order::where('user_id',$User->id)->whereNotIn('delivery_status',['Order Cancelled','Order Returned'])->sum('distributor_discount'));
    	$withdrawals=floor(Withdrawal::where('member_id',$User->member->id)->sum('amount'));
        $pins_available=Pin::where('used_at',null)->where('owned_by',$User->member->id)->count();
        $current_personal_pv=$User->member->current_personal_pv;
        $total_personal_pv=$User->member->total_personal_pv;
        $balance=floatval($User->member->wallet_balance);
        $total_payout=MemberPayout::where('member_id',$User->member->id)->sum('total_payout');

        $TransactionType=TransactionType::where('name','Cashback Income')->first();
        $cashback_income=WalletTransaction::where('member_id',$User->member->id)->where('transaction_type_id',$TransactionType->id)->sum('amount');
        
        $response = array(
            'status' => true,
            'message'=>'Stats recieved',
            'stats'=>array(
                'downlines'=>$downlines,
                'referrals'=>$referrals,
                'total_purchase'=>$total_purchase,
                'withdrawals'=>$withdrawals,
                'pins_available'=>$pins_available,
                'balance'=>$balance,
                'total_payout'=>$total_payout,
                'current_personal_pv'=>$current_personal_pv,
                'total_personal_pv'=>$total_personal_pv,
                'member'=>$Member,
                'total_group_bv'=>$total_group_bv,
                'total_matched'=>$total_matched,
                'distributor_discount'=>$distributor_discount,
                'cashback_income'=>$cashback_income,
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
        $dt = Carbon::now()->modify('-7 months');        
        $from= $dt->firstOfMonth()->toDateString('Y-m-d');        
        $to=Carbon::now()->modify('-1 months')->endOfMonth()->toDateString('Y-m-d');
        $payouts=MemberPayout::whereBetween('created_at', [$from,$to])
                    ->where('member_id',$User->member->id)
                    ->orderBy('date', 'ASC')
                    ->get(array(
                        DB::raw('Date(created_at) as date'),
                        DB::raw('sum(total_payout) as income')
                    ));
        $od=[];


        for ($i=7; $i >= 1 ; $i--) {
            
            if(count($payouts)){
                foreach ($payouts as $payout) {
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
