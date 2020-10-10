<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\Product;
use App\Models\User\Cart;
use App\Models\User\Order;
use App\Models\User\OrderProduct;
use App\Models\User\OrderPackage;
use App\Models\Admin\Pin;
use App\Models\User\DeliveryLog;
use App\Models\Superadmin\TransactionType;
use App\Models\Superadmin\PaymentMode;
use App\Models\User\Address;
use App\Models\Admin\WalletTransaction;
use App\Models\Admin\Setting;
use App\Models\Admin\CompanySetting;
use Validator;
use JWTAuth;
use Carbon\Carbon;
use App\Events\OrderPlacedEvent;

class ShoppingController extends Controller
{

    public function getCategories(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

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

        if(!$search){           
            $Categories=Category::with('parent')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Categories=Category::select();
            $Categories=$Categories->orWhere('name','like','%'.$search.'%');
            $Categories=$Categories->with('parent')->orderBy('id',$sort)->paginate($limit);
        }
        
        $response = array('status' => true,'message'=>"Categories retrieved.",'data'=>$Categories);
        return response()->json($response, 200);
    }

    public function getMyOrders(Request $request)
    {

        $user=JWTAuth::user();
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $date_range=$request->date_range;
        $delivery_status=$request->delivery_status;

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
            $Orders=$Orders->with('products','shipping_address','logs','payment_mode','packages');
            $Orders=$Orders->where('user_id',$user->id);
            $Orders=$Orders->orderBy('id',$sort)->paginate($limit);
            $order_total=Order::select([\DB::raw('sum(final_amount) as final_total'),\DB::raw('sum(pv) as pv')])->where('user_id',$user->id)->first();
        }else{
            $order_total=Order::select([\DB::raw('sum(final_amount) as final_total'),\DB::raw('sum(pv) as pv')])->where('user_id',$user->id);
            $Orders=Order::select();
            
            $Orders=$Orders->where(function ($query)use($search) {
                $query->orWhere('order_no','like','%'.$search.'%');               

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
            $Orders=$Orders->where('user_id',$user->id);

            $Orders=$Orders->with('products','shipping_address','logs','payment_mode','packages');
            $Orders=$Orders->orderBy('id',$sort)->paginate($limit);
        }
        
        $response = array('status' => true,'message'=>"Orders retrieved.",'data'=>$Orders,'sum'=>$order_total);
        return response()->json($response, 200);
    }

    public function myAffiliateBonus(Request $request) {
        // dd("Asdasd");
        $user=JWTAuth::user();
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $date_range=$request->date_range;
        $delivery_status=$request->delivery_status;

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

        $pageSize = $limit; 
        $pageNew = $page;
        $skip = $page * ($pageNew);

        $TransactionType=TransactionType::where('name','Affiliate Bonus')->first();

        $affiliateBonus= WalletTransaction::Select('*', \DB::raw('sum(amount) as totalAmount'),\DB::raw('date(created_at) as dates'));
        $affiliateBonus=$affiliateBonus->where('transaction_type_id',$TransactionType->id);
        $affiliateBonus=$affiliateBonus->where('member_id',$user->member->id);
        $affiliateBonus=$affiliateBonus->groupBy('dates');
        $affiliateBonus=$affiliateBonus->orderBy('id',$sort);
        $affiliateBonus=$affiliateBonus->skip(($page-1)*$limit);
        $affiliateBonus=$affiliateBonus->take($limit);
        $affiliateBonus=$affiliateBonus->get();


        $total= WalletTransaction::Select('*', \DB::raw('sum(amount) as totalAmount'),\DB::raw('date(created_at) as dates'));
        $total=$total->where('transaction_type_id',$TransactionType->id);
        $total=$total->where('member_id',$user->member->id);
        $total=$total->groupBy('dates');
        $total=$total->orderBy('id',$sort);
        $total=$total->get();


        // dd(count($affiliateBonus));
        $response = array('status' => true,'message'=>"Orders retrieved.",'data'=>$affiliateBonus,'total'=>count($total));
        return response()->json($response, 200);
    }

    public function getOrder($id)
    {
        // dd($id);
        $user=JWTAuth::user();
        $rolesArray = array();
        foreach (JWTAuth::user()->roles as $key => $value) {
            array_push($rolesArray,$value->name);
        }
        // dd($rolesArray);
        $user_details=array('name' => $user->name,'username'=>$user->username );

        $settings= Setting::orWhere('is_public',1)
        ->get()->pluck('value', 'key')->toArray();

        $Orders = Order::select();
        $Orders = $Orders->with('products','shipping_address','shipping_address.user','billing_address','packages');
        $Orders = $Orders->where('id',$id);
        if(!in_array("admin",$rolesArray)){
            $Orders = $Orders->where('user_id',$user->id);
        }
        $Orders=$Orders->first();

        if($Orders){
            $response = array('status' => true,'message'=>"Orders retrieved.",'data'=>$Orders, 'user'=>$user_details,'company_details'=>$settings);
        }else{
            $response = array('status' => false,'message'=>"Orders retrieved.",'data'=>[], 'user'=>[],'company_details'=>[]);
        }     
        return response()->json($response, 200);
    }

    public function myCartProducts(){
        $User=JWTAuth::user();
        $Cart=Cart::where('user_id',$User->id)->pluck('product_id')->toArray();
        $response = array('status' => true,'message'=>'Cart product received','data'=>$Cart);
        return response()->json($response, 200);
    }

    public function myCart(){
        $User=JWTAuth::user();
        $Cart=Cart::where('user_id',$User->id)->with('products')->get();
        $response = array('status' => true,'message'=>'Cart product received','data'=>$Cart);
        return response()->json($response, 200);
    }
    
    public function myCartCount(){
        $User=JWTAuth::user();
        $Cart=Cart::where('user_id',$User->id)->get();
        $response = array('status' => true,'message'=>'Cart count received','data'=>$Cart->count());
        return response()->json($response, 200);
    }    

    public function addToCart(Request $request){
        $User=JWTAuth::user();
        $validate = Validator::make($request->all(), [           
            'product_id' => "required"
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Cart=new Cart;
        $Cart->product_id=$request->product_id;
        $Cart->user_id=$User->id;
        $Cart->qty=1;
        $Cart->save();

        $response = array('status' => true,'message'=>'Item added to cart.');
        return response()->json($response, 200);
    }

    public function updateCartQty(Request $request){
        $User=JWTAuth::user();
        $validate = Validator::make($request->all(), [           
            'product_id' => "required|integer",
            'qty' => "required|integer",
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Cart=Cart::where('product_id',$request->product_id)->where('user_id',$User->id)->first();
        $Cart->qty=$request->qty;
        $Cart->save();

        $response = array('status' => true,'message'=>'Cart updated.');
        return response()->json($response, 200);
    }
    public function removeFromCart($id)
    {
        $User=JWTAuth::user();
        $Cart= Cart::where('product_id',$id)->where('user_id',$User->id)->delete();                          
        $response = array('status' => true,'message'=>'Product removed from cart.');             
        return response()->json($response, 200);
    }

    public function placeOrder(Request $request){
        $validate = Validator::make($request->all(), [           
            'payment_mode' => "required|max:32",
            'grand_total' => "required|numeric",
            'shipping_address_id' => 'required|integer'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $User=JWTAuth::user();

        $balance=$User->member->wallet_balance;
        $shipping_charge=CompanySetting::getValue('shipping_charge');

        if($balance < $request->grand_total){
            $response = array('status' => false,'message'=>'You do not have enough balance place order');
            return response()->json($response, 400);
        }

        $Cart=Cart::where('user_id',$User->id)->get();

        $subtotal=0;
        $total_gst=0;
        $shipping=0;
        $admin=0;
        $discount=0;
        $grand_total=0;
        $pv=0;
        $distributor_discount=0;

        foreach ($Cart as $item) {
            $subtotal+=floatval($item->products->retail_base)*intval($item->qty);
            $total_gst+=floatval($item->products->retail_gst)*intval($item->qty);
            //$shipping+=floatval($item->products->shipping_fee)*intval($item->qty);
            $admin+=floatval($item->products->admin_fee)*intval($item->qty);
            $discount+=floatval($item->products->discount_amount)*intval($item->qty);
            $pv+=floatval($item->products->pv?:0)*intval($item->qty);
            $grand_total=$subtotal+$total_gst+$shipping+$admin-$discount;
            $distributor_discount=0;
            // $distributor_discount+=(($item->products->retail_amount)*intval($item->qty))-(($item->products->retail_amount)*intval($item->qty));
        }


        $grand_total+=$shipping_charge;  
        $shipping=$shipping_charge;


        if($grand_total != $request->grand_total){
            $response = array('status' => false,'message'=>'Order data mismatch. try again');
            return response()->json($response, 400);
        }

        $PaymentMode=PaymentMode::where('name','Wallet')->first();

        $order_no = substr(str_shuffle("01234012345678900123456789123456012345678978956789"), 0, 10);

        $TransactionType=TransactionType::where('name','Debit (Purchase)')->first();

        if($TransactionType){
            $WalletTransaction=new WalletTransaction;
            $WalletTransaction->member_id=$User->member->id;
            $WalletTransaction->balance=$balance-$grand_total;
            $WalletTransaction->amount=$grand_total;
            $WalletTransaction->transaction_type_id=$TransactionType->id;
            $WalletTransaction->transaction_by=$User->id;
            $WalletTransaction->note='Product Purchase';
            $WalletTransaction->save();

            $final_balance=$balance-$grand_total;
            $User->member->wallet_balance=$final_balance;
            $User->member->save();

            $Order=new Order;
            $Order->order_no=$order_no;
            $Order->user_id=$User->id;
            $Order->amount=$subtotal;
            $Order->discount=$discount;
            $Order->gst=$total_gst;
            $Order->shipping_fee=$shipping;
            $Order->payout_id=$request->payout_id?1:0;
            $Order->is_withhold_purchase=$request->payout_id?1:0;
            $Order->admin_fee=$admin;
            $Order->final_amount=$grand_total;
            $Order->distributor_discount=$distributor_discount;
            $Order->pv=$pv;
            $Order->payment_status='Success';
            $Order->wallet_transaction_id=$WalletTransaction->id;            
            $Order->payment_mode=$PaymentMode->id;
            $Order->shipping_address_id=$request->shipping_address_id;
            $Order->billing_address_id=$request->billing_address_id;
            $Order->delivery_status='Order Created';
            $Order->save();

            $Order->order_no=$Order->id;
            $Order->save();
            
            foreach ($Cart as $item) {
                $OrderProduct=new OrderProduct;
                $OrderProduct->order_id=$Order->id;
                $OrderProduct->product_id=$item->products->id;
                $OrderProduct->amount=floatval($item->products->retail_base)*intval($item->qty);
                $OrderProduct->gst=floatval($item->products->retail_gst)*intval($item->qty);
                $OrderProduct->gst_rate=$item->products->retail_gst_rate;
                $OrderProduct->shipping_fee=floatval($item->products->shipping_fee)*intval($item->qty);
                $OrderProduct->admin_fee=floatval($item->products->admin_fee)*intval($item->qty);
                $OrderProduct->discount=floatval($item->products->discount_amount)*intval($item->qty);
                $OrderProduct->final_amount=$OrderProduct->amount+$OrderProduct->gst+$OrderProduct->shipping_fee+$OrderProduct->admin_fee+$OrderProduct->discount;
                $OrderProduct->pv=floatval($item->products->pv?:0)*intval($item->qty);
                $OrderProduct->qty=$item->qty;
                $OrderProduct->save();

                $OrderProduct->product->stock-=$item->qty;
                $OrderProduct->product->save();
            }

            $DeliveryLog=new DeliveryLog;
            $DeliveryLog->order_id=$Order->id;
            $DeliveryLog->delivery_status='Order Created';
            $DeliveryLog->save();

            event(new OrderPlacedEvent($Order,$User));

            Cart::where('user_id',$User->id)->delete();

            $response = array('status' => true,'message'=>'Your order has been placed successfully','data'=>$Order);
            return response()->json($response, 200);

        }else{
            $response = array('status' => false,'message'=>'Something went wrong, contact admin.');
            return response()->json($response, 400);
        }        
    }

    public function getPersonalPVMonthly(Request $request)
    {
        $user=JWTAuth::user();
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        $Orders=Order::selectRaw('*,YEAR(created_at) year, MONTH(created_at) month, sum(pv) as total_pv')
        ->groupBy('year','month')
        ->where('user_id',$user->id)->whereNotIn('delivery_status',['Order Returned','Order Created','Order Cancelled'])->paginate($limit);

        $response = array('status' => true,'message'=>"Personal PV History retrieved.",'data'=>$Orders);
        return response()->json($response, 200);
    }

    public function placePackageOrder(Request $request){
        $validate = Validator::make($request->all(), [ 
            'pin_number' => "required",
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $User=JWTAuth::user();

        $Pin= Pin::where('pin_number',$request->pin_number)->first();

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
            $Order->shipping_address_id=$Address->id;
            $Order->billing_address_id=$Address->id;
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

            $response = array('status' => true,'message'=>'Pin Redeemed successfully');
            return response()->json($response, 200);

        }else{
            $response = array('status' => false,'message'=>'Payment mode Pin not found, contact admin.');
            return response()->json($response, 400);
        }        
    }
}
