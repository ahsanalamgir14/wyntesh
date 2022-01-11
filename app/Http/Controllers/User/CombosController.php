<?php

namespace App\Http\Controllers\User;
use JWTAuth;
use Validator;
use App\Models\User\Order;
use App\Models\Admin\Combo;
use App\Models\User\Address;
use Illuminate\Http\Request;
use App\Models\Admin\Product;
use App\Models\Admin\StockLogs;
use App\Events\OrderPlacedEvent;
use App\Models\User\DeliveryLog;
use App\Models\User\OrderProduct;
use App\Models\Admin\ComboCategory;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Admin\CompanySetting;
use App\Models\Admin\ProductVariant;
use App\Models\Superadmin\PaymentMode;
use App\Models\Admin\WalletTransaction;
use App\Models\Superadmin\TransactionType;

class CombosController extends Controller
{
    public function getSingleCombo($id)
    {
        $Combo=Combo::with('categories','categories.category')->find($id);
        
         if($Combo){
            
            $response = array('status' => true,'message'=>'Combo retrieved.','data'=>$Combo);             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Combo not found');
            return response()->json($response, 404);
        }
    }

    public function getProductsBySizeAndColor($categoryId, $sizeId,$colorId){

        $categoryProducts = Product::whereHas('categories', function($q)use($categoryId){
            $q->where('categories.id',$categoryId);
        })->get()->pluck('id')->toArray();

        $Products=ProductVariant::select();
        
        if($colorId && $colorId>0){
            $Products=$Products->whereHas('color', function($q)use($colorId){
                $q->where('id',$colorId);
            });
        }
        if($sizeId && $sizeId>0){
            $Products=$Products->whereHas('size', function($q)use($sizeId){
                $q->where('id',$sizeId);
            });
        }

        $Products=$Products->whereHas('product', function($q)use($categoryProducts){
            $q->whereIn('id',$categoryProducts);
        });
        
        $Products=$Products->with('product')->where('is_active',1)->get();   
        
        if($Products){
            $response = array('status' => true,'message'=>'Products retrieved.','data'=>$Products);             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Products not found');
            return response()->json($response, 404);
        }
    }
    public function placeCombo(Request $request){
        
        $validate = Validator::make($request->all(), [           
            'payment_mode' => "required|max:32",
            'combo_id' => 'required|integer',
            'productVariantIds' => 'required|array|min:3',
            'shipping_address_id' => 'required|integer'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $combo_id = $request->combo_id;
        $payment_mode = $request->payment_mode;
        $shipping_address_id = $request->shipping_address_id;
        $productVariantIds = $request->productVariantIds;

        $User=JWTAuth::user();

        $balance=$User->member->wallet_balance;
        $shipping_charge=CompanySetting::getValue('shipping_charge');
        $shipping_charge_2=CompanySetting::getValue('shipping_charge_2');
        $shipping_criteria=CompanySetting::getValue('shipping_criteria');
        $home_state=CompanySetting::getValue('home_state')?CompanySetting::getValue('home_state'):1;

        $combo=Combo::find($combo_id);

        if($combo){
            if($balance < $combo->net_amount){
                $response = array('status' => false,'message'=>'You do not have enough balance place order');
                return response()->json($response, 400);
            }
        } else {
            $response = array('status' => false,'message'=>'Invalid Combo');
            return response()->json($response, 400);
        }

        $shipping_address = Address::where("id",$request->shipping_address_id)->first();
        $billing_address = Address::where("id",$request->shipping_address_id)->first();

        if(!$shipping_address){
            $response = array('status' => false,'message'=>'Shipping address not found');
            return response()->json($response, 404);
        }

        if($request->billing_address_id){
            $billing_address= Address::where("id",$request->billing_address_id)->first();
        }

        if(!$billing_address){
            $response = array('status' => false,'message'=>'Billing address not found');
            return response()->json($response, 404);
        }


        $PaymentMode=PaymentMode::where('name','Wallet')->first();

        $order_no = substr(str_shuffle("01234012345678900123456789123456012345678978956789"), 0, 10);

        $TransactionType=TransactionType::where('name','Debit (Purchase)')->first();

        if($TransactionType){
            $WalletTransaction=new WalletTransaction;
            $WalletTransaction->member_id=$User->member->id;
            $WalletTransaction->balance=$balance-$combo->net_amount;
            $WalletTransaction->amount=$combo->net_amount;
            $WalletTransaction->transaction_type_id=$TransactionType->id;
            $WalletTransaction->transaction_by=$User->id;
            $WalletTransaction->note='Product combo Purchase';
            $WalletTransaction->save();

            $final_balance=$balance-$combo->net_amount;
            $User->member->wallet_balance=$final_balance;
            $User->member->save();

            $Order=new Order;
            $Order->order_no=$order_no;
            $Order->user_id=$User->id;
            $Order->base_amount=$combo->base_amount;
            $Order->discount_amount=0;
            $Order->cgst_amount=$combo->cgst_amount;
            $Order->sgst_amount=$combo->sgst_amount;
            $Order->utgst_amount=$combo->utgst_amount;
            $Order->gst_amount=$combo->gst_amount;
            $Order->shipping_fee=0;
            $Order->net_amount=$combo->net_amount;
            $Order->distributor_discount=0;
            $Order->pv=$combo->pv;
            $Order->payment_status='Success';
            $Order->wallet_transaction_id=$WalletTransaction->id;            
            $Order->payment_mode=$PaymentMode->id;
            $Order->state=$shipping_address->state;
            $Order->city=$shipping_address->city;
            $Order->gstin=$request->gstin;

            $Order->shipping_address=$shipping_address->door_no.', '.$shipping_address->full_name.', '.$shipping_address->address.', '.$shipping_address->landmark.', '.$shipping_address->state.', '.$shipping_address->city.', '.$shipping_address->pincode.', '.$shipping_address->mobile_number;
            $Order->billing_address=$billing_address->door_no.', '.$billing_address->full_name.', '.$billing_address->address.', '.$billing_address->landmark.', '.$billing_address->state.', '.$billing_address->city.', '.$billing_address->pincode.', '.$billing_address->mobile_number;
            $Order->delivery_status='Order Created';
            $Order->save();

            $Order->order_no=$Order->id;
            $Order->save();
            
            // foreach ($Cart as $item) {

            //     $orderProduct=new OrderProduct;
            //     $orderProduct->order_id=$Order->id;
            //     $orderProduct->product_id=$item->products->id;
            //     $orderProduct->base_amount=floatval($item->products->dp_base)*intval($item->qty);

            //     if($shipping_address->state==$home_state){
            //         $orderProduct->sgst_amount=floatval($item->products->dp_sgst_amount)*intval($item->qty);
            //         $orderProduct->sgst_rate=$item->products->dp_sgst_rate;
            //         $orderProduct->cgst_amount=floatval($item->products->dp_cgst_amount)*intval($item->qty);
            //         $orderProduct->cgst_rate=$item->products->dp_cgst_rate;
            //     }else{
            //         $orderProduct->gst_amount=floatval($item->products->dp_gst_amount)*intval($item->qty);
            //         $orderProduct->gst_rate=$item->products->dp_gst_rate;                    
            //     }
                
            //     $orderProduct->discount=floatval($item->products->discount_amount)*intval($item->qty);
            //     $orderProduct->net_amount=$orderProduct->base_amount+$orderProduct->gst_amount+$orderProduct->sgst_amount+$orderProduct->cgst_amount-$orderProduct->discount;
            //     $orderProduct->pv=floatval($item->products->pv?:0)*intval($item->qty);
            //     $orderProduct->quantity=$item->qty;
            //     $orderProduct->variant_id=$item->variant->id;
            //     $orderProduct->save();

            //     $item->variant->stock-=$item->qty;
            //     $item->variant->save();

            //     $StockLogs = new StockLogs;
            //     $StockLogs->product_id      = $item->variant->product_id;
            //     $StockLogs->variant_id      = $item->variant->id;
            //     $StockLogs->sku             = $item->variant->sku_code;
            //     $StockLogs->units_outward   = $item->qty;
            //     $StockLogs->order_id   = $Order->id;
            //     $StockLogs->is_order_placed   = 1;
            //     $StockLogs->note            = "Product purchase";
            //     $StockLogs->save();
            // }

            $DeliveryLog=new DeliveryLog;
            $DeliveryLog->order_id=$Order->id;
            $DeliveryLog->delivery_status='Order Created';
            $DeliveryLog->save();

            event(new OrderPlacedEvent($Order,$User));


            $response = array('status' => true,'message'=>'Your order has been placed successfully','data'=>$Order);
            return response()->json($response, 200);

        }else{
            $response = array('status' => false,'message'=>'Something went wrong, contact admin.');
            return response()->json($response, 400);
        }        
    }
    
}
