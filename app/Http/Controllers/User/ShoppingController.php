<?php

namespace App\Http\Controllers\User;

use JWTAuth;
use Validator;
use Carbon\Carbon;
use App\Models\Admin\Pin;
use App\Models\User\Cart;
use App\Models\User\Order;
use App\Models\User\Address;
use Illuminate\Http\Request;
use App\Models\Admin\Product;
use App\Models\Admin\Setting;
use App\Models\Admin\Category;
use App\Models\Admin\StockLogs;
use App\Events\OrderPlacedEvent;
use App\Models\User\DeliveryLog;
use App\Models\Admin\SizeVariant;
use App\Models\User\OrderPackage;
use App\Models\User\OrderProduct;
use App\Models\Admin\ColorVariant;
use App\Models\Admin\ProductImage;
use App\Http\Controllers\Controller;
use App\Models\Admin\CompanySetting;
use App\Models\Admin\ProductVariant;
use App\Models\Superadmin\PaymentMode;
use App\Models\Admin\WalletTransaction;
use App\Models\Superadmin\TransactionType;

class ShoppingController extends Controller
{

    public function getSingleProduct($id)
    {
        $Product=Product::with('categories','images.variant.color','images.variant.size','variants.color','variants.size')->find($id);
        
         if($Product){
            $productColorVariant = ProductVariant::where('product_id', $Product->id)->with('size','color')->groupBy('color_id')->get();
            
            $response = array('status' => true,'message'=>'Product retrieved.','data'=>$Product,'productColorVariant'=>$productColorVariant);             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Product not found');
            return response()->json($response, 404);
        }
    }

    public function getSizebyColor(Request $request){

        $productVariants = ProductVariant::where('color_id',$request->color_id)->where('product_id',$request->product_id)->groupBy('color_id')->get()->pluck('id')->toArray();
        $productImages=ProductImage::whereIn('variant_id',$productVariants)->get();

        $productVariant = ProductVariant::where('color_id',$request->color_id)->where('product_id',$request->product_id)->with('size')->get();

        $response = array('status' => true,'message'=>"Size and color retrieved.",'data'=>$productVariant,'images'=>$productImages);
        return response()->json($response, 200);
    }

    public function getColorBySize($id){
        $productVariant = ProductVariant::where('size_id',$id)->with('size','color')->get();
        $response = array('status' => true,'message'=>"Size and color retrieved.",'data'=>$productVariant);
        return response()->json($response, 200);
    }

    public function getStock(Request $request){
        $productaStock = ProductVariant::where('product_id',$request->product_id)->where('color_id',$request->color_id)->where('size_id',$request->size_id)->first();
        $response = array('status' => true,'message'=>"Stock retrieved.",'data'=>$productaStock);
        return response()->json($response, 200);
    }

    public function getSizesByCategory($categoryId){
        $category = Category::find($categoryId);
    
        if(!$category) {
            $response = array('status' => false,'message'=>"Category not found");
            return response()->json($response, 404);
        }

        $sizesId = ProductVariant::whereHas('product.categories', function($q)use($category){
            $q->where('categories.id',$category->id);
        })->whereHas('product', function($q){
            $q->where('is_active',1);
        })->where('stock','>',0)->get()->pluck('size_id')->toArray();

        $sizes = SizeVariant::whereIn('id',$sizesId )->get();

        $response = array('status' => true,'message'=>"Sizes retrieved.",'data'=>$sizes);
        return response()->json($response, 200);
    }

    public function getColorsByCategory($categoryId){
        $category = Category::find($categoryId);
    
        if(!$category) {
            $response = array('status' => false,'message'=>"Category not found");
            return response()->json($response, 404);
        }
        $colorsId = ProductVariant::whereHas('product.categories', function($q)use($category){
            $q->where('categories.id',$category->id);
        })->whereHas('product', function($q){
            $q->where('is_active',1);
        })->where('stock','>',0)->get()->pluck('color_id')->toArray();

        $colors = ColorVariant::whereIn('id',$colorsId)->get();

        $response = array('status' => true,'message'=>"Colors retrieved.",'data'=>$colors);
        return response()->json($response, 200);
    }

    public function myCartProducts(){
        $user=JWTAuth::user();
        $cart=Cart::where('user_id',$user->id)->pluck('variant_id')->toArray();
        $response = array('status' => true,'message'=>'Cart product received','data'=>$cart);
        return response()->json($response, 200);
    }

    public function myCart(){
        $user=JWTAuth::user();
        $cart=Cart::where('user_id',$user->id)->pluck('product_id')->toArray();
        $cartVariants=Cart::where('user_id',$user->id)->get();

        foreach ($cartVariants as $variant) {
            $isLowStock=ProductVariant::where('id',$variant->variant_id)->where('stock','<',$variant->qty)->first();

            if($isLowStock){
                Cart::where('user_id',$user->id)->where('variant_id',$variant->variant_id)->delete();
            }

        }
        
        $product=Product::whereIn('id',$cart)->pluck('id')->toArray();
        $cart=Cart::where('user_id',$user->id)->whereIn('product_id',$product)->with('variant.size','variant.color','products')->get();
        $response = array('status' => true,'message'=>'Cart product received','data'=>$cart);
        return response()->json($response, 200);
    }

    public function myCartCount(){
        $user=JWTAuth::user();
        $cart=Cart::where('user_id',$user->id)->get();
        $response = array('status' => true,'message'=>'Cart count received','data'=>$cart->count());
        return response()->json($response, 200);
    }    

    public function addToCart(Request $request){
        $user=JWTAuth::user();
        $validate = Validator::make($request->all(), [           
            'product_id' => "required",
            'variant_id' => "required"
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $productStock = ProductVariant::where('id', $request->variant_id)->pluck('stock')->first();
        if(1 > $productStock){
            $response = array('status' => false,'message'=>'Enough stock is not available','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $cart=new Cart;
        $cart->product_id=$request->product_id;
        $cart->variant_id=$request->variant_id;
        $cart->user_id=$user->id;
        $cart->qty=1;
        $cart->save();

        $response = array('status' => true,'message'=>'Item added to cart.');
        return response()->json($response, 200);
    }

    public function updateCartQty(Request $request){ 
   
        $user=JWTAuth::user();
        $validate = Validator::make($request->all(), [           
            'variant_id' => "required|integer",
            'qty' => "required|integer",
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        if($request->qty <= 0){
            $response = array('status' => false,'message'=>'Enter valid quantity.');
            return response()->json($response, 400);
        }

        $productStock = ProductVariant::where('id', $request->variant_id)->pluck('stock')->first();
        if((int)$request->qty > $productStock){
            $response = array('status' => false,'message'=>'Enough stock is not available','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $cart=Cart::where('variant_id',$request->variant_id)->where('user_id',$user->id)->first();
        
        if($cart){            
            $cart->qty=$request->qty;
            $cart->save();
        }

        $response = array('status' => true,'message'=>'Cart updated.');
        return response()->json($response, 200);
    }
    
    public function removeFromCart($id)
    {
        $user=JWTAuth::user();
        $cart= Cart::where('variant_id',$id)->where('user_id',$user->id)->delete();
        $response = array('status' => true,'message'=>'Product removed from cart.');             
        return response()->json($response, 200);
    }

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

        $orders=Order::select();
        
        if($search){
            $orders=$orders->where(function ($query)use($search) {
                $query->orWhere('order_no','like','%'.$search.'%');
            });
        }  

        if($delivery_status){
            $orders=$orders->where('delivery_status',$delivery_status);
        }

        if($date_range){
            $orders=$orders->whereDate('created_at','>=', $date_range[0]);
            $orders=$orders->whereDate('created_at','<=', $date_range[1]);
        }

        $orders=$orders->where('user_id',$user->id);

        $orders=$orders->with('products.variant.color','products.variant.size','logs','payment_mode');
        $orders=$orders->orderBy('id',$sort)->paginate($limit);
        
        $response = array('status' => true,'message'=>"Orders retrieved.",'data'=>$orders);
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
        $user=JWTAuth::user();
        $rolesArray = array();
        foreach (JWTAuth::user()->roles as $key => $value) {
            array_push($rolesArray,$value->name);
        }

        $user_details=array('name' => $user->name,'username'=>$user->username,'contact'=>$user->contact,'email'=>$user->email );

        $settings= Setting::orWhere('is_public',1)
        ->get()->pluck('value', 'key')->toArray();

        $orders = Order::select();
        $orders = $orders->with('products.variant.color','products.variant.size','packages.variant.color','packages.variant.size','packages.product','user');
        $orders = $orders->where('id',$id);
        if(!in_array("admin",$rolesArray)){
            $orders = $orders->where('user_id',$user->id);
        }
        $orders=$orders->first();

        if($orders){
            $response = array('status' => true,'message'=>"Orders retrieved.",'data'=>$orders, 'user'=>$user_details,'company_details'=>$settings);
        }else{
            $response = array('status' => false,'message'=>"Orders retrieved.",'data'=>[], 'user'=>[],'company_details'=>[]);
        }     

        return response()->json($response, 200);
    }


    public function placeOrder(Request $request){
        $validate = Validator::make($request->all(), [           
            'payment_mode' => "required|max:32",
            'grand_total' => "required|numeric",
            'shipping_address_id' => 'required|integer'
        ]);
        
        $home_state=CompanySetting::getValue('home_state')?CompanySetting::getValue('home_state'):1;

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $User=JWTAuth::user();

        $balance=$User->member->wallet_balance;
        $shipping_charge=CompanySetting::getValue('shipping_charge');
        $shipping_charge_2=CompanySetting::getValue('shipping_charge_2');
        $shipping_criteria=CompanySetting::getValue('shipping_criteria');
        $home_state=CompanySetting::getValue('home_state');

        if($balance < $request->grand_total){
            $response = array('status' => false,'message'=>'You do not have enough balance place order');
            return response()->json($response, 400);
        }

        $shipping_address = Address::where("id",$request->shipping_address_id)->first();

        if(!$shipping_address){
            $response = array('status' => false,'message'=>'Shipping address not found');
            return response()->json($response, 404);
        }

        if(!$shipping_address){
            $billing_address=$shipping_address;
        }else{
            $billing_address= Address::where("id",$request->billing_address_id)->first();            
        }

        if(!$billing_address){
            $response = array('status' => false,'message'=>'Billing address not found');
            return response()->json($response, 404);
        }

        $Cart=Cart::where('user_id',$User->id)->get();

        $subtotal=0;
        $gst_amount=0;
        $sgst_amount=0;
        $cgst_amount=0;
        $utgst_amount=0;
        $shipping=0;
        $discount_amount=0;
        $grand_total=0;
        $pv=0;
        $distributor_discount=0;

        $shipping=0;

        $is_shipping_waiver=1;

        foreach ($Cart as $item) {
            $subtotal+=floatval($item->products->dp_base)*intval($item->qty);
            if($shipping_address->state==$home_state){
                $sgst_amount+=floatval($item->products->dp_sgst_amount)*intval($item->qty);
                $cgst_amount+=floatval($item->products->dp_cgst_amount)*intval($item->qty);
            }else{
                $gst_amount+=floatval($item->products->dp_gst_amount)*intval($item->qty);
            }

            $discount_amount+=floatval($item->products->discount_amount)*intval($item->qty);
            $pv+=floatval($item->products->pv?:0)*intval($item->qty);
            $grand_total=round($subtotal+$gst_amount+$cgst_amount+$sgst_amount+$utgst_amount+$shipping-$discount_amount);
            $distributor_discount+=(($item->products->retail_amount)*intval($item->qty))-(($item->products->dp_amount)*intval($item->qty));

            if(!$item->products->is_shipping_waiver){
                $is_shipping_waiver=0;
            }
        }

        if(!$is_shipping_waiver){
            if($grand_total < $shipping_criteria){
                $shipping=$shipping_charge_2;
            }else{
                $shipping=$shipping_charge;
            }
        }

        $grand_total+=$shipping;
        
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
            $Order->base_amount=$subtotal;
            $Order->discount_amount=$discount_amount;
            $Order->cgst_amount=$cgst_amount;
            $Order->sgst_amount=$sgst_amount;
            $Order->utgst_amount=$utgst_amount;
            $Order->gst_amount=$gst_amount;
            $Order->shipping_fee=$shipping;
            $Order->net_amount=$grand_total;
            $Order->distributor_discount=$distributor_discount;
            $Order->pv=$pv;
            $Order->payment_status='Success';
            $Order->wallet_transaction_id=$WalletTransaction->id;            
            $Order->payment_mode=$PaymentMode->id;
            $Order->state=$shipping_address->state;
            $Order->city=$shipping_address->city;
            $Order->gstin=$request->gstin;

            $Order->shipping_address=$shipping_address->door_no.', '.$shipping_address->address.', '.$shipping_address->landmark.', '.$shipping_address->state.', '.$shipping_address->city.', '.$shipping_address->pincode.', '.$shipping_address->mobile_number;
            $Order->billing_address=$billing_address->door_no.', '.$billing_address->address.', '.$billing_address->landmark.', '.$billing_address->state.', '.$billing_address->city.', '.$billing_address->pincode.', '.$billing_address->mobile_number;
            $Order->delivery_status='Order Created';
            $Order->save();

            $Order->order_no=$Order->id;
            $Order->save();
            
            foreach ($Cart as $item) {

                $orderProduct=new OrderProduct;
                $orderProduct->order_id=$Order->id;
                $orderProduct->product_id=$item->products->id;
                $orderProduct->base_amount=floatval($item->products->dp_base)*intval($item->qty);

                if($shipping_address->state==$home_state){
                    $orderProduct->sgst_amount=floatval($item->products->dp_sgst_amount)*intval($item->qty);
                    $orderProduct->sgst_rate=$item->products->dp_sgst_rate;
                    $orderProduct->cgst_amount=floatval($item->products->dp_cgst_amount)*intval($item->qty);
                    $orderProduct->cgst_rate=$item->products->dp_cgst_rate;
                }else{
                    $orderProduct->gst_amount=floatval($item->products->dp_gst_amount)*intval($item->qty);
                    $orderProduct->gst_rate=$item->products->dp_gst_rate;                    
                }
                
                $orderProduct->discount=floatval($item->products->discount_amount)*intval($item->qty);
                $orderProduct->net_amount=$orderProduct->base_amount+$orderProduct->gst_amount+$orderProduct->sgst_amount+$orderProduct->cgst_amount-$orderProduct->discount;
                $orderProduct->pv=floatval($item->products->pv?:0)*intval($item->qty);
                $orderProduct->quantity=$item->qty;
                $orderProduct->variant_id=$item->variant->id;
                $orderProduct->save();

                $item->variant->stock-=$item->qty;
                $item->variant->save();

                $StockLogs = new StockLogs;
                $StockLogs->product_id      = $item->variant->product_id;
                $StockLogs->variant_id      = $item->variant->id;
                $StockLogs->sku             = $item->variant->sku_code;
                $StockLogs->units_outward   = $item->qty;
                $StockLogs->order_id   = $Order->id;
                $StockLogs->is_order_placed   = 1;
                $StockLogs->note            = "Product purchase";
                $StockLogs->save();
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
            $Order->net_amount=$grand_total;
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
            $OrderPackage->discount=0;
            $OrderPackage->net_amount=$Pin->package->net_amount;
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

    public function getUserCourses($user){
        $products=Product::where('is_course',1)->get()->pluck('id');
        $orderIds=Order::where('user_id',$user->id)->whereNotIn('delivery_status',['Order Returned','Order Created','Order Cancelled'])->get()->pluck('id');
        $orderProducts=OrderProduct::whereIn('order_id',$orderIds)->whereIn('product_id',$products)->get()->pluck('product.name')->toArray();

        $userPackage=implode(',', $orderProducts);

        return $userPackage;

    }
}
