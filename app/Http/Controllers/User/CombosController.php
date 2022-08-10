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
        })->where('is_active',1)->get()->pluck('id')->toArray();

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
        
        $Products=$Products->where('stock', '>' ,0)->with('product')->get();   
        
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
            'productVariantIds' => 'required|array|min:1',
            'productVariantIds.*.category_id' => 'required|integer',
            'productVariantIds.*.product_variant_id' => 'required|integer',
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
            if($balance < $request->grand_total){
                $response = array('status' => false,'message'=>'You do not have enough balance place order');
                return response()->json($response, 400);
            }
        } else {
            $response = array('status' => false,'message'=>'Invalid Combo');
            return response()->json($response, 400);
        }

        $shipping_address = Address::where("id",$shipping_address_id)->first();
        $billing_address = Address::where("id",$shipping_address_id)->first();

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

        $subtotal=0;
        $gst_amount=0;
        $sgst_amount=0;
        $cgst_amount=0;
        $utgst_amount=0;
        $shipping=0;
        $discount_amount=0;
        $grand_total=0;
        $pv=floatval($combo->pv?:0);
        $distributor_discount=0;
        $shipping=0;
        $is_shipping_waiver=1;
        $virtualCart = [];
        
        foreach ($productVariantIds as $item) {
            $virtualItem = array();
            $virtualItem['product_variant_id'] = $item['product_variant_id'];
            $virtualItem['category_id'] = $item['category_id'];
            $virtualItem['qty'] = 1; 
            array_push($virtualCart, $virtualItem);
        }

        foreach ($virtualCart as $virtualCartItem) {
            // lookout for same category in the combo_categories
            $item = ComboCategory::where('combo_id', $combo_id)->where('category_id', $virtualCartItem['category_id'])->first();
            
            $subtotal+=floatval($item->dp_base);
            if($shipping_address->state==$home_state){
                $sgst_amount+=floatval($item->dp_sgst_amount);
                $cgst_amount+=floatval($item->dp_cgst_amount);
            }else{
                $gst_amount+=floatval($item->dp_gst_amount);
            }

            $grand_total=round($subtotal+$gst_amount+$cgst_amount+$sgst_amount+$utgst_amount+$shipping-$discount_amount);
            // $distributor_discount+=(($item->mrp))-(($item->dp_amount));
            $distributor_discount+=0;
        }
        if(!$combo->is_shipping_waiver){
            $is_shipping_waiver=0;
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
        
        $TransactionType=TransactionType::where('name','Debit (Purchase)')->first();

        if($TransactionType){
            $WalletTransaction=new WalletTransaction;
            $WalletTransaction->member_id=$User->member->id;
            $WalletTransaction->balance=$balance-$grand_total;
            $WalletTransaction->amount=$grand_total;
            $WalletTransaction->transaction_type_id=$TransactionType->id;
            $WalletTransaction->transaction_by=$User->id;
            $WalletTransaction->note='Product Combo Purchase';
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
            $Order->distributor_discount=0;
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
            $Order->is_combo=1;
            $Order->save();

            $Order->order_no=$Order->id;
            $Order->save();
            
            foreach ($virtualCart as $virtualCartItem) {

                // lookout for same category in the combo_categories
                $item = ComboCategory::where('combo_id', $combo_id)->where('category_id', $virtualCartItem['category_id'])->first();
                $productVariantId = $virtualCartItem['product_variant_id'];
                $productVariant = ProductVariant::find($productVariantId);

                // $response = array('status' => false,'message'=>'debug stop');
                // return response()->json($response, 404);

                $orderProduct=new OrderProduct;
                $orderProduct->order_id=$Order->id;
                $orderProduct->product_id=$productVariant->product_id;
                $orderProduct->base_amount=floatval($item->dp_base);

                if($shipping_address->state==$home_state){
                    $orderProduct->sgst_amount=floatval($item->dp_sgst_amount);
                    $orderProduct->sgst_rate=$item->dp_sgst_rate;
                    $orderProduct->cgst_amount=floatval($item->dp_cgst_amount);
                    $orderProduct->cgst_rate=$item->dp_cgst_rate;
                }else{
                    $orderProduct->gst_amount=floatval($item->dp_gst_amount);
                    $orderProduct->gst_rate=$item->dp_gst_rate;                    
                }
                
                $orderProduct->discount=0;
                $orderProduct->net_amount=$orderProduct->base_amount+$orderProduct->gst_amount+$orderProduct->sgst_amount+$orderProduct->cgst_amount-$orderProduct->discount;
                $orderProduct->pv=floatval($item->pv?:0);
                $orderProduct->quantity=1;
                $orderProduct->variant_id=$productVariantId;
                $orderProduct->save();

                $productVariant->stock-=1;
                $productVariant->save();

                $StockLogs = new StockLogs;
                $StockLogs->product_id      = $productVariant->product_id;
                $StockLogs->variant_id      = $productVariant->id;
                $StockLogs->sku             = $productVariant->sku_code;
                $StockLogs->units_outward   = 1;
                $StockLogs->order_id   = $Order->id;
                $StockLogs->is_order_placed   = 1;
                $StockLogs->note            = "Product Combo purchase";
                $StockLogs->save();
            }

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
