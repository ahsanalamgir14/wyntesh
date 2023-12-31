<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User\User;
use App\Models\User\Order;
use App\Models\Admin\MemberPayout;
use App\Models\User\Ticket;
use App\Models\User\Kyc;
use App\Models\Admin\Pin;
use App\Models\Admin\Sale;
use App\Models\Admin\Withdrawal;
use App\Models\Admin\WithdrawalRequest;
use App\Models\Admin\Payout;
use App\Models\Admin\WalletTransaction;
use App\Models\Admin\CompanySetting;
use App\Models\Admin\Member;
use App\Models\Admin\Inquiry;
use App\Models\Superadmin\TransactionType;
use App\Models\Admin\AffiliateBonus;
use App\Models\Admin\Reward;
use JWTAuth;
use Carbon\Carbon;
use DB;

class DashboardController extends Controller
{
    
    public function stats(){
        
        $users=User::role('user')->count();
        $inactive_users=User::role('user')->where('is_active',0)->count();
        $wallet_balance=Member::sum('wallet_balance');
        $total_orders=floor(Order::whereNotIn('delivery_status',["Order Returned","Order Cancelled"])->sum('net_amount'));
        $total_business_volume=floor(Order::whereNotIn('delivery_status',['Order Cancelled','Order Returned'])->sum('pv'));
        $pending_withdrawals=WithdrawalRequest::where('request_status','Pending')->count();
        $pending_orders=Order::where('delivery_status','Order Created')->count();
        $tickets=Ticket::count();
        $withdrawals=floor(Withdrawal::sum('amount'));
        $used_pin=Pin::where('used_at','!=',null)->count();
        $unused_pin=Pin::where('used_at',null)->count();
        $pending_kyc=Kyc::where('verification_status','pending')->count();
        $inquiries=Inquiry::count();
        $total_income_wallet_balance=Member::sum('income_wallet_balance');

        $affiliateIncomeWithTDS=AffiliateBonus::sum('amount');
        $reward=Reward::sum('amount');

        $total_payout=MemberPayout::sum('payout_amount');
        $cur_affiliate_bonus=AffiliateBonus::whereMonth('created_at',date('m'))->sum('amount');        
        $cur_reward=Reward::whereMonth('created_at',date('m'))->sum('amount');
        $total_payout+=$cur_affiliate_bonus+$cur_reward;


        $response = array(
            'status' => true,
            'message'=>'Stats recieved',
            'stats'=>array(
                'users'=>$users,
                'inactive_users'=>$inactive_users,
                'total_orders'=>$total_orders,
                'tickets'=>$tickets,
                'used_pin'=>$used_pin,
                'unused_pin'=>$unused_pin,
                'pending_kyc'=>$pending_kyc,
                'inquiries'=>$inquiries,
                'wallet_balance'=>$wallet_balance,
                'withdrawals'=>$withdrawals,
                'total_payout'=>$total_payout,
                'pending_withdrawals'=>$pending_withdrawals,
                'pending_orders'=>$pending_orders,
                'total_income_wallet_balance'=>$total_income_wallet_balance,
                'total_affilite_bonus'=>$affiliateIncomeWithTDS,
                'total_business_volume'=>$total_business_volume
            )
        );

        return response()->json($response, 200);

    }

    public function orderStats(){
    	$from=Carbon::now()->subDays(7)->format('Y-m-d');
        $to=Carbon::now()->format('Y-m-d'); 
    	$orders=Order::whereBetween('created_at', [$from,$to])
    				->groupBy('date')
                    ->orderBy('date', 'ASC')
                    ->get(array(
                        DB::raw('Date(created_at) as date'),
                        DB::raw('sum(net_amount) as sum')
                    ));
        $od=[];

        for ($i=7; $i >= 1 ; $i--) {
            if(count($orders)){
            	foreach ($orders as $order) {
            	 	if($order->date==Carbon::now()->subDays($i)->format('Y-m-d') ){
            	 		$od[$i]['date']=Carbon::parse($order->date)->format('m-d');
            	 		$od[$i]['sum']=floor($order->sum);
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
        $order=[];
        foreach ($od as $o) {
        	$order[]=$o;
        }
        $response = array('status' => true,'message'=>'Stats recieved','orders'=>$order);             
        return response()->json($response, 200);
    }

    public function pinActivations(){
    	$from=Carbon::now()->subDays(7)->format('Y-m-d');
        $to=Carbon::now()->format('Y-m-d'); 
    	$pins=Pin::whereBetween('used_at', [$from,$to])
    				->groupBy('date')
                    ->orderBy('date', 'ASC')
                    ->get(array(
                        DB::raw('Date(used_at) as date'),
                        DB::raw('count(*) as count')
                    ));
        $ar=[];

        for ($i=7; $i >= 1 ; $i--) {
            if(count($pins)){
            	foreach ($pins as $pin) {
            	 	if($pin->date==Carbon::now()->subDays($i)->format('Y-m-d') ){
            	 		$ar[$i]['date']=Carbon::parse($pin->date)->format('m-d');
            	 		$ar[$i]['count']=floor($pin->count);
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

        $activations=[];
        foreach ($ar as $o) {
        	$activations[]=$o;
        }
        $response = array('status' => true,'message'=>'Stats recieved','activations'=>$activations);             
        return response()->json($response, 200);
    }

    public function monthlyJoiningsCount(){
       
        $monthwise_count=Member::
                    select(DB::raw('count(id) as `count`'), DB::raw("DATE_FORMAT(created_at, '%m-%Y') month"),  DB::raw('YEAR(created_at) year, MONTH(created_at) month'))
                    ->groupby('year','month')
                    ->orderBy('id', 'DESC')
                    ->limit(5)
                    ->get();

        $response = array('status' => true,'message'=>'Latest downlines recieved','data'=>$monthwise_count);             
        return response()->json($response, 200);
    }

    public function monthlyBusiness(){
       
       $payouts = Payout::with('payout_type','incomes.income')
                    ->orderBy('id', 'DESC')
                    ->limit(5)
                    ->get();

        $response = array('status' => true,'message'=>'Monthly payouts recieved.','data'=>$payouts);             
        return response()->json($response, 200);
    }
}
