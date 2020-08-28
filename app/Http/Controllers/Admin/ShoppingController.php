<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\Product;
use App\Models\User\Cart;
use App\Models\User\Order;
use App\Models\User\OrderProduct;
use App\Models\User\DeliveryLog;
use App\Models\User\OrderPackage;
use App\Models\Admin\Pin;
use App\Models\Superadmin\TransactionType;
use App\Models\Superadmin\PaymentMode;
use App\Models\Admin\Sale;
use App\Models\Admin\CompanySetting;
use App\Models\Admin\ActivationLog;
use App\Models\Admin\WalletTransaction;
use App\Models\Admin\Member;
use App\Models\Admin\Income;
use App\Models\Admin\AffiliateBonus;
use App\Models\Admin\Payout;
use App\Models\User\Address;
use App\Models\User\User;
use Validator;
use JWTAuth;
use Carbon\Carbon;
use App\Events\OrderUpdateEvent;
use App\Events\UpdateGroupPVEvent;
use DB;

class ShoppingController extends Controller
{

    public function getNewOrders(Request $request)
    {

        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $date_range=$request->date_range;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1000;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if(!$search && !$date_range ){           
            $Orders=Order::select();
            $Orders=$Orders->with('products','shipping_address','logs','user:id,username,name','payment_mode','packages');
            $Orders=$Orders->where('delivery_status','Order Created');
            $Orders=$Orders->orderBy('id',$sort)->paginate($limit);
        }else{
            $Orders=Order::select();
            
            $Orders=$Orders->where(function ($query)use($search) {
                $query->orWhere('order_no','like','%'.$search.'%');               
                $query=$query->orWhereHas('user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });

            if($date_range){
                $Orders=$Orders->whereDate('created_at','>=', $date_range[0]);
                $Orders=$Orders->whereDate('created_at','<=', $date_range[1]);
            }

            $Orders=$Orders->where('delivery_status','Order Created');

            $Orders=$Orders->with('products','shipping_address','logs','user:id,username,name','payment_mode','packages');
            $Orders=$Orders->orderBy('id',$sort)->paginate($limit);
        }
        
        $response = array('status' => true,'message'=>"Orders retrieved.",'data'=>$Orders);
        return response()->json($response, 200);
    }

    public function getMonthlyOverview(Request $request) {

        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $month=$request->month;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1000;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if(!$month ){     
            $month = date('m');
            $year = date('Y');
            
            $Orders=Order::whereNotIn('delivery_status',['Order Cancelled','Order Returned'])
            ->whereYear('created_at', '=', $year)
            ->whereMonth('created_at', '=', $month);

            $order_count=$Orders->count();
            $order_total_pv=$Orders->sum('pv');
            $order_total_amount=$Orders->sum('final_amount');
            $order_total_gst=$Orders->sum('gst');
            $total_tds=0;
            $total_admin_fee=0;
            $Payout=Payout::whereYear('sales_start_date', '=', $year)
            ->whereMonth('sales_start_date', '=', $month)->first();

            if($Payout){
                $total_tds=$Payout->tds;
                $total_admin_fee=$Payout->admin_fee;
            }

            $monthly_business=[['order_count'=>$order_count,'order_total_pv'=>$order_total_pv,'order_total_amount'=>$order_total_amount,'order_total_gst'=>$order_total_gst,'total_tds'=>$total_tds,'total_admin_fee'=>$total_admin_fee]];
        }else{

            $month=$month.'-01';
            $date=Carbon::parse($month);
            $month = $date->month;
            $year = $date->year;

            $Orders=Order::whereNotIn('delivery_status',['Order Cancelled','Order Returned'])
            ->whereYear('created_at', '=', $year)
            ->whereMonth('created_at', '=', $month);

            $order_count=$Orders->count();
            $order_total_pv=$Orders->sum('pv');
            $order_total_amount=$Orders->sum('final_amount');
            $order_total_gst=$Orders->sum('gst');
            $total_tds=0;
            $total_admin_fee=0;
            $Payout=Payout::whereYear('sales_start_date', '=', $year)
            ->whereMonth('sales_start_date', '=', $month)->first();

            if($Payout){
                $total_tds=$Payout->tds;
                $total_admin_fee=$Payout->admin_fee;
            }

            $monthly_business=[['order_count'=>$order_count,'order_total_pv'=>$order_total_pv,'order_total_amount'=>$order_total_amount,'order_total_gst'=>$order_total_gst,'total_tds'=>$total_tds,'total_admin_fee'=>$total_admin_fee]];
        }
        
        $response = array('status' => true,'message'=>"Monthly Business summury retrieved.",'data'=>$monthly_business);
        return response()->json($response, 200);
    }



    public function getAllOrders(Request $request)
    {

        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $date_range=$request->date_range;
        $delivery_status=$request->delivery_status;
        $order_total='';

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1000;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if(!$search && !$date_range && !$delivery_status){           
            $Orders=Order::select();
            $Orders=$Orders->with('products','shipping_address','logs','user:id,username,name','payment_mode','packages');
            $Orders=$Orders->orderBy('id',$sort)->paginate($limit);
            $order_total=Order::select([\DB::raw('sum(final_amount) as final_total'),\DB::raw('sum(pv) as pv')])->first();
        }else{
             $order_total=Order::select([\DB::raw('sum(final_amount) as final_total'),\DB::raw('sum(pv) as pv')]);

            $Orders=Order::select();            
            $order_total=Order::select([DB::raw('sum(final_amount) as final_total'),DB::raw('sum(gst) as gst')]);
            $Orders=$Orders->where(function ($query)use($search) {
                $query->orWhere('order_no','like','%'.$search.'%');  
                $query=$query->orWhereHas('user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });            

            });

            if($delivery_status){
                $Orders=$Orders->where('delivery_status',$delivery_status);
            }

            if($date_range){
                $Orders=$Orders->whereDate('created_at','>=', $date_range[0]);
                $Orders=$Orders->whereDate('created_at','<=', $date_range[1]);
                 $order_total=$order_total->whereDate('created_at','>=', $date_range[0]);
                $order_total=$order_total->whereDate('created_at','<=', $date_range[1]);
            }
            $order_total= $order_total->first();
            $Orders=$Orders->with('products','shipping_address','logs','user:id,username,name','payment_mode','packages');
            $Orders=$Orders->orderBy('id',$sort)->paginate($limit);
        }
        
        $response = array('status' => true,'message'=>"Orders retrieved.",'data'=>$Orders,'sum'=>$order_total);
        return response()->json($response, 200);
    }

    public function getGSTReport(Request $request)
    {

        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $date_range=$request->date_range;
        $delivery_status=$request->delivery_status;
        $order_total='';

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1000;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if(!$search && !$date_range && !$delivery_status){           
            $Orders=Order::select();
            $Orders=$Orders->whereNotIn('delivery_status',['Order Cancelled','Order Returned']);
            $Orders=$Orders->with('products','shipping_address','logs','user:id,username,name','payment_mode','packages');
            $Orders=$Orders->orderBy('id',$sort)->paginate($limit);
            $order_total=Order::select([DB::raw('sum(final_amount) as final_total'),DB::raw('sum(gst) as gst')])->first();
        }else{
            $Orders=Order::select();
            $Orders=$Orders->whereNotIn('delivery_status',['Order Cancelled','Order Returned']);            
            $order_total=Order::select([DB::raw('sum(final_amount) as final_total'),DB::raw('sum(gst) as gst')]);
            $Orders=$Orders->where(function ($query)use($search) {
                $query->orWhere('order_no','like','%'.$search.'%');  
                $query=$query->orWhereHas('user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });            

            });

            if($delivery_status){
                $Orders=$Orders->where('delivery_status',$delivery_status);
            }

            if($date_range){
                $Orders=$Orders->whereDate('created_at','>=', $date_range[0]);
                $Orders=$Orders->whereDate('created_at','<=', $date_range[1]);
                $order_total=$order_total->whereDate('created_at','>=', $date_range[0]);
                $order_total=$order_total->whereDate('created_at','<=', $date_range[1]);
            }
            $order_total=$order_total->first();
            $Orders=$Orders->with('products','shipping_address','logs','user:id,username,name','payment_mode','packages');
            $Orders=$Orders->orderBy('id',$sort)->paginate($limit);
        }
        
        $response = array('status' => true,'message'=>"Orders retrieved.",'data'=>$Orders,'sum'=>$order_total);
        return response()->json($response, 200);
    }

    public function updateOrder(Request $request){
        $User=JWTAuth::user();
        $validate = Validator::make($request->all(), [   
            'delivery_status' => "required",
            'id' => "required|integer",
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Order=Order::find($request->id);

        if($Order){

            $old_status=$Order->delivery_status;
            if(($old_status=='Order Returned') || ( $old_status=='Order Cancelled')){
                $response = array('status' => false,'message'=>'Cancelled or returned order cannot be updated.');
                return response()->json($response, 400);
            }



            $Order->delivery_status=$request->delivery_status;
            $Order->delivery_by=$request->delivery_by;
            $Order->tracking_no=$request->tracking_no;
            $Order->remarks=$request->remarks;
            $Order->save();

            $DeliveryLog=new DeliveryLog;
            $DeliveryLog->order_id=$request->id;
            $DeliveryLog->delivery_status=$request->delivery_status;
            $DeliveryLog->remarks=$request->remarks;
            $DeliveryLog->save();

            $ExistingSale=Sale::where('order_id',$Order->id)->first();
            // dd($request->delivery_status);
            if($request->delivery_status=='Order Confirmed' && !$ExistingSale ){

                $final_amount_company=($Order->final_amount)-($Order->gst)-($Order->shipping_fee)-($Order->admin_fee);
                $Sale=new Sale;
                $Sale->member_id=$Order->user->member->id;
                $Sale->pv=$Order->pv;
                $Sale->order_id=$Order->id;
                $Sale->final_amount_company=$final_amount_company;

                if($Order->is_withhold_purchase){
                    $Sale->is_withhold_purchase=1;
                }

                $Sale->save();

                $Order->user->member->current_personal_pv+=$Order->pv;
                $Order->user->member->total_personal_pv+=$Order->pv;
                $Order->user->member->save(); 

                if(!$Order->user->is_active){
                    $minimum_purchase=CompanySetting::getValue('minimum_purchase');
                    if($Order->user->member->total_personal_pv>=$minimum_purchase){
                        $Order->user->is_active=1;
                        $Order->user->save();
                        $ActivationLog              = new ActivationLog;
                        $ActivationLog->user_id     = $Order->user->id;
                        $ActivationLog->is_active   = 1;
                        $ActivationLog->by_user     = $User->id;
                        $ActivationLog->remarks     = 'Minimum Purchase Activation.';
                        $ActivationLog->save();
                    }
                }

                // Add Affiliate bonus to sponser
                $Incomes=Income::where("code","AFFILIATE")->with('income_parameters')->first();

                $Incomes->income_parameters[0]->value_1 = isset($Incomes->income_parameters[0]->value_1)?$Incomes->income_parameters[0]->value_1:0;

                $incmParam = isset($Incomes->income_parameters[0]->value_1)?$Incomes->income_parameters[0]->value_1:0;

                if($Order->user->member->sponsor){

                    $tds_percentage = CompanySetting::getValue('tds_percentage');
                    $amount = ($Order->pv*$incmParam)/100; 
                    $tds=($amount*$tds_percentage)/100;
                    $deducted_tds_amount = $amount-($amount*$tds_percentage)/100;

                    $Order->user->member->sponsor->wallet_balance += $deducted_tds_amount;
                    $Order->user->member->sponsor->save();
                    $TransactionType=TransactionType::where('name','Affiliate Bonus')->first();

                    $WalletTransaction                      = new WalletTransaction;
                    $WalletTransaction->member_id           = $Order->user->member->sponsor_id;
                    $WalletTransaction->balance             = $Order->user->member->sponsor->wallet_balance;
                    $WalletTransaction->transaction_type_id = $TransactionType->id;
                    $WalletTransaction->amount              = $deducted_tds_amount;
                    $WalletTransaction->transfered_to       = $Order->user->member->sponsor->user->id;
                    $WalletTransaction->note                = 'Affiliate Bonus';
                    $WalletTransaction->save();

                    $AffiliateBonus=new AffiliateBonus;
                    $AffiliateBonus->member_id=$Order->user->member->sponsor_id;
                    $AffiliateBonus->income_id=$Incomes->id;
                    $AffiliateBonus->order_id=$Order->id;
                    $AffiliateBonus->amount=$amount;
                    $AffiliateBonus->tds_percent=$tds_percentage;
                    $AffiliateBonus->tds_amount=$tds;
                    $AffiliateBonus->final_amount=$deducted_tds_amount;
                    $AffiliateBonus->created_at=$Order->created_at;
                    $AffiliateBonus->save();
                }

                
                $WalletTransaction->transfered_from         = $Order->user->member->sponsor->user->id;
                event(new UpdateGroupPVEvent($Order,$Order->user,'add'));
            }

            if(($request->delivery_status=='Order Cancelled' || $request->delivery_status=='Order Returned')){
 
               if($old_status !== 'Order Created'){

                    // Remove Affiliate bonus to sponser
                    $Incomes=Income::where("code","AFFILIATE")->with('income_parameters')->first();
                    $Incomes->income_parameters[0]->value_1 = isset($Incomes->income_parameters[0]->value_1)?$Incomes->income_parameters[0]->value_1:0;
                    $incmParam = isset($Incomes->income_parameters[0]->value_1)?$Incomes->income_parameters[0]->value_1:0;

                    $Sale=Sale::where('order_id',$Order->id)->first();
                    $Sale->member_id=$Order->user->member->id;
                    $Sale->pv=0;
                    $Sale->order_id=$Order->id;
                    $Sale->final_amount_company=0;
                    $Sale->save();

                    if($Order->user->member->sponsor){

                        $tds_percentage = CompanySetting::getValue('tds_percentage');
                        $amount = ($Order->pv*$incmParam)/100; 
                        $deducted_tds_amount = $amount-($amount*$tds_percentage)/100;

                        $Order->user->member->sponsor->wallet_balance -= $deducted_tds_amount;
                        $Order->user->member->sponsor->save();
                        
                        $Order->user->member->current_personal_pv-=$Order->pv;
                        $Order->user->member->total_personal_pv-=$Order->pv;
                        $Order->user->member->save();
                   
                        $TransactionType=TransactionType::where('name','Affiliate Bonus Debit')->first();
                        $WalletTransaction=new WalletTransaction;
                        $WalletTransaction->member_id            = $Order->user->member->sponsor_id;
                        $WalletTransaction->balance              = $Order->user->member->sponsor->wallet_balance;
                        $WalletTransaction->amount               = $deducted_tds_amount;
                        $WalletTransaction->transaction_type_id  = $TransactionType->id;
                        $WalletTransaction->transfered_from      = $Order->user->member->sponsor->user->id;
                        $WalletTransaction->note                 = 'Affiliate Bonus Debit';
                        $WalletTransaction->save();


                        $AffiliateBonus=AffiliateBonus::where('order_id',$Order->id)->first();
                        if($AffiliateBonus){
                            $AffiliateBonus->delete();
                        }
                        
                    }

                    event(new UpdateGroupPVEvent($Order,$Order->user,'subtract'));
                }
                

                $minimum_purchase=CompanySetting::getValue('minimum_purchase');
                if($Order->user->is_active){
                    if($Order->user->member->total_personal_pv<$minimum_purchase){
                        $Order->user->is_active=0;
                        $Order->user->save();

                        $ActivationLog=new ActivationLog;
                        $ActivationLog->user_id=$Order->user->id;
                        $ActivationLog->is_active=0;
                        $ActivationLog->by_user=$User->id;
                        $ActivationLog->remarks='Minimum Purchase Dectivation. Order returned or cancelled.';
                        $ActivationLog->save();

                    }
                }


                $TransactionType=TransactionType::where('name','Order Refund')->first();
                $balance=$Order->user->member->wallet_balance;
                $WalletTransaction=new WalletTransaction;
                $WalletTransaction->member_id=$Order->user->member->id;
                $WalletTransaction->balance=$balance+$Order->final_amount;
                $WalletTransaction->amount=$Order->final_amount;
                $WalletTransaction->transaction_type_id=$TransactionType->id;
                $WalletTransaction->transfered_to=$Order->user->id;
                $WalletTransaction->transaction_by=$User->id;
                $WalletTransaction->note='Product Return';
                $WalletTransaction->save();

                $final_balance=$balance+$Order->final_amount;
                $Order->User->member->wallet_balance=$final_balance;
                $Order->User->member->save();
                
                
            }

            event(new OrderUpdateEvent($Order,$Order->user));

            $response = array('status' => true,'message'=>'Order updated successfully.');
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Order not found');
            return response()->json($response, 404);
        }
    }

    public function placePackageOrder(Request $request){
        $validate = Validator::make($request->all(), [ 
            'pin_number' => "required",
            'member_id'=>"required"
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $User=JWTAuth::user();

        $Pin= Pin::where('pin_number',$request->pin_number)->first();
        $User=User::where('username',$request->member_id)->first();

        if(!$Pin){
            $response = array('status' => false,'message'=>'Pin not found');
            return response()->json($response, 404);
        }

        if(!$User){
            $response = array('status' => false,'message'=>'Member not found');
            return response()->json($response, 404);
        }

        if($Pin->used_by && $Pin->used_at){
            $response = array('status' => false,'message'=>'Pin is already used.');
            return response()->json($response, 400);
        }


        $subtotal=$Pin->base_amount;
        $total_gst=$Pin->tax_amount;
        $shipping=0;
        $admin=0;
        $discount=0;
        $grand_total=$Pin->total_amount;
        $pv=$Pin->package->pv;

        $PaymentMode=PaymentMode::where('name','Pin')->first();
        $Address=Address::where('user_id',$User->id)->where('is_default',1)->first();

        $order_no = substr(str_shuffle("01234012345678900123456789123456012345678978956789"), 0, 10);

        if($PaymentMode){

            $Order=new Order;
            $Order->order_no=$order_no;
            $Order->user_id=$User->id;
            $Order->amount=$subtotal;
            $Order->discount=$discount;
            $Order->gst=$total_gst;
            $Order->shipping_fee=$shipping;
            $Order->admin_fee=$admin;
            $Order->final_amount=$grand_total;
            $Order->pv=$pv;
            $Order->payment_status='Success';            
            $Order->payment_mode=$PaymentMode->id;
            $Order->shipping_address_id=$Address?$Address->id:null;
            $Order->billing_address_id=$Address?$Address->id:null;
            $Order->delivery_status='Order Created';
            $Order->save();

            $OrderPackage=new OrderPackage;
            $OrderPackage->order_id=$Order->id;
            $OrderPackage->package_id=$Pin->package->id;
            $OrderPackage->amount=$Pin->package->base_amount;
            $OrderPackage->gst=$Pin->package->gst_amount;
            $OrderPackage->gst_rate=$Pin->package->gst_rate;
            $OrderPackage->shipping_fee=0;
            $OrderPackage->admin_fee=0;
            $OrderPackage->discount=0;
            $OrderPackage->final_amount=$Pin->package->net_amount;
            $OrderPackage->pv=$Pin->package->pv;
            $OrderPackage->qty=1;
            $OrderPackage->save();

            $DeliveryLog=new DeliveryLog;
            $DeliveryLog->order_id=$Order->id;
            $DeliveryLog->delivery_status='Order Created';
            $DeliveryLog->save();

            $Pin->used_by=$User->id;
            $Pin->used_at=Carbon::now();
            $Pin->status='Used';
            $Pin->save();

            $User->is_active=1;
            $User->save();
            
            $response = array('status' => true,'message'=>'Account activated successfully');
            return response()->json($response, 200);

        }else{
            $response = array('status' => false,'message'=>'Payment mode Pin not found, contact admin.');
            return response()->json($response, 400);
        }        
    }

}
