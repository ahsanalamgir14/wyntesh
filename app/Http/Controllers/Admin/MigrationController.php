<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

use App\Models\Admin\User;
use App\Models\Admin\Member;
use App\Models\Admin\Income;
use App\Models\Admin\Payout;
use App\Models\Admin\PayoutIncome;
use App\Models\Admin\MemberPayout;
use App\Models\Admin\MemberPayoutIncome;
use App\Models\Admin\IncomeParameter;
use App\Models\Admin\Sale;
use App\Models\Admin\PayoutType;
use App\Models\User\Address;
use App\Models\User\Order;
use Illuminate\Support\Facades\Storage;

class MigrationController extends Controller
{    

    public function doMigration(){
        $this->ordersMigration();
        $this->orderProductssMigration();
        $this->orderAddressUpdate();
        $this->updateGST();

        $this->payoutMigration();
        $this->memberPayoutsMigration();
        $this->memberPayoutIncomeMigration();
        $this->payoutIncomeMigration();
        $this->verify();

    }

    public function ordersMigration(){
        $base_amount="ALTER TABLE `orders` CHANGE `amount` `base_amount` DECIMAL(12,2) NOT NULL DEFAULT '0.00';";
        $gst_amount="ALTER TABLE `orders` CHANGE `gst` `gst_amount` DECIMAL(12,2) NOT NULL DEFAULT '0.00';";
        $discount_amount="ALTER TABLE `orders` CHANGE `discount` `discount_amount` DECIMAL(12,2) NULL DEFAULT '0.00';";
        $admin_fee="ALTER TABLE `orders` DROP `admin_fee`;";
        $is_withhold_purchase="ALTER TABLE `orders` DROP `is_withhold_purchase`, DROP `payout_id`;";
        $net_amount="ALTER TABLE `orders` CHANGE `final_amount` `net_amount` DECIMAL(12,2) NOT NULL DEFAULT '0.00';";
        $gst_columns_add="ALTER TABLE `orders` ADD `cgst_amount` DECIMAL(12,2) NOT NULL DEFAULT '0' AFTER `gst_amount`, ADD `sgst_amount` DECIMAL(12,2) NOT NULL DEFAULT '0' AFTER `cgst_amount`, ADD `utgst_amount` DECIMAL(12,2) NOT NULL DEFAULT '0' AFTER `sgst_amount`;";
        $new_columns="ALTER TABLE `orders` ADD `is_returned` TINYINT NOT NULL DEFAULT '0' AFTER `delivery_status`, ADD `returned_at` TIMESTAMP NULL DEFAULT NULL AFTER `is_returned`, ADD `state` VARCHAR(32) NULL AFTER `returned_at`, ADD `city` VARCHAR(32) NULL AFTER `state`, ADD `is_member` TINYINT NOT NULL DEFAULT '1' AFTER `city`;";
        $shippingAddress="ALTER TABLE `orders` CHANGE `billing_address_id` `billing_address` VARCHAR(1024) NULL DEFAULT NULL, CHANGE `shipping_address_id` `shipping_address` VARCHAR(1024) NULL DEFAULT NULL;";
        $gstin="ALTER TABLE `orders` ADD `gstin` VARCHAR(32) NULL DEFAULT NULL AFTER `billing_address`;";

        DB::statement( $base_amount );
        DB::statement( $gst_amount );
        DB::statement( $discount_amount );
        DB::statement( $admin_fee );
        DB::statement( $is_withhold_purchase );
        DB::statement( $net_amount );
        DB::statement( $gst_columns_add );
        DB::statement( $new_columns );
        DB::statement( $shippingAddress );
        DB::statement( $gstin );
    }

    public function orderProductssMigration(){
        $home_state="INSERT INTO `company_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES (NULL, 'home_state', 'Tamil Nadu', '2020-12-17 16:48:06', NULL);";
        DB::statement( $home_state );

        $variant_id="ALTER TABLE `order_products` ADD `variant_id` INT NOT NULL DEFAULT '1' AFTER `product_id`;";
        DB::statement( $variant_id );
        
        $quantity="ALTER TABLE `order_products` CHANGE `qty` `quantity` INT(11) NOT NULL;";
        DB::statement( $quantity );

        $base_amount="ALTER TABLE `order_products` CHANGE `amount` `base_amount` DECIMAL(12,2) NOT NULL DEFAULT '0.00';";
        DB::statement( $base_amount );

        $net_amount="ALTER TABLE `order_products` CHANGE `final_amount` `net_amount` DECIMAL(12,2) NOT NULL DEFAULT '0.00';";
        DB::statement( $net_amount );
        
        $gst_amount="ALTER TABLE `order_products` CHANGE `gst` `gst_amount` DECIMAL(12,2) NOT NULL DEFAULT '0.00';";
        DB::statement( $gst_amount );
        
        $gst_rate="ALTER TABLE `order_products` CHANGE `gst_rate` `gst_rate` DECIMAL(12,2) NOT NULL DEFAULT '0.00';";
        DB::statement( $gst_rate );

        $gst_columns_add="ALTER TABLE `order_products` ADD `cgst_amount` DECIMAL(12,2) NOT NULL DEFAULT '0' AFTER `gst_amount`, ADD `sgst_amount` DECIMAL(12,2) NOT NULL DEFAULT '0' AFTER `cgst_amount`, ADD `utgst_amount` DECIMAL(12,2) NOT NULL DEFAULT '0' AFTER `sgst_amount`;";
        DB::statement( $gst_columns_add );

        $gst_rate_columns_add="ALTER TABLE `order_products` ADD `cgst_rate` DECIMAL(12,2) NOT NULL DEFAULT '0' AFTER `gst_rate`, ADD `sgst_rate` DECIMAL(12,2) NOT NULL DEFAULT '0', ADD `utgst_rate` DECIMAL(12,2) NOT NULL DEFAULT '0' ;";
        DB::statement( $gst_rate_columns_add );

        $sku="ALTER TABLE `order_products` ADD `sku` VARCHAR(32) NULL DEFAULT NULL AFTER `pv`;";
        DB::statement( $sku );
    }

    public function orderAddressUpdate(){

        $address="ALTER TABLE `addresses` ADD `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;";
        DB::statement( $address );

        $orders=Order::all();
        foreach ($orders as $order) {
            $shipping_address=Address::where('id',$order->shipping_address)->first();
            $billing_address=Address::where('id',$order->billing_address)->first();

            if($shipping_address){
                $order->shipping_address=$shipping_address->full_name.', '.$shipping_address->address.', '.$shipping_address->landmark.', '.$shipping_address->state.', '.$shipping_address->city.', '.$shipping_address->pincode.', '.$shipping_address->mobile_number;
                $order->save();
            }

            if($billing_address){
                $order->billing_address=$billing_address->full_name.', '.$billing_address->address.', '.$billing_address->landmark.', '.$billing_address->state.', '.$billing_address->city.', '.$billing_address->pincode.', '.$billing_address->mobile_number;
                $order->save();
            }
        }

    }

    public function updateGST(){
        $orders=Order::all();
        foreach ($orders as $order) {
            $order->cgst_amount=$order->gst_amount/2;
            $order->sgst_amount=$order->gst_amount/2;
            $order->gst_amount=0;
            $order->save();

            foreach ($order->products as $product) {
                $product->cgst_amount=$product->gst_amount/2;
                $product->sgst_amount=$product->gst_amount/2;
                $product->gst_amount=0;

                $product->cgst_rate=$product->gst_rate/2;
                $product->sgst_rate=$product->gst_rate/2;
                $product->gst_rate=0;
                $product->save();
            }
        }
    }

    public function payoutMigration(){

        $payout_amount="ALTER TABLE `payouts` CHANGE `total_payout` `payout_amount` DECIMAL(12,2) NOT NULL DEFAULT '0.00';";
        DB::statement( $payout_amount );

        $tds_percent="ALTER TABLE `payouts` ADD `tds_percent` DECIMAL(5,2) NOT NULL DEFAULT '0' AFTER `tds`;";
        DB::statement( $tds_percent );

        $admin_fee_percent="ALTER TABLE `payouts` ADD `admin_fee_percent` DECIMAL(5,2) NOT NULL AFTER `admin_fee`;";
        DB::statement( $admin_fee_percent );

        $net_payable_amount="ALTER TABLE `payouts` ADD `net_payable_amount` DECIMAL(12,2) NOT NULL DEFAULT '0' AFTER `admin_fee_percent`;";
        DB::statement( $net_payable_amount );

        $payouts=Payout::all();

        foreach ($payouts as $payout) {
            $payout->tds_percent=5;
            $payout->admin_fee_percent=0;
            $payout->net_payable_amount=$payout->tds+$payout->payout_amount;
            $payout->save();
        }
    }

    public function memberPayoutsMigration(){

        $payout_amount="ALTER TABLE `member_payouts` CHANGE `total_payout` `payout_amount` DECIMAL(12,2) NOT NULL DEFAULT '0.00';";
        DB::statement( $payout_amount );

        $tds_percent="ALTER TABLE `member_payouts` ADD `tds_percent` DECIMAL(5,2) NOT NULL DEFAULT '0' AFTER `tds`;";
        DB::statement( $tds_percent );

        $admin_fee_percent="ALTER TABLE `member_payouts` ADD `admin_fee_percent` DECIMAL(5,2) NOT NULL AFTER `admin_fee`;";
        DB::statement( $admin_fee_percent );

        $net_payable_amount="ALTER TABLE `member_payouts` ADD `net_payable_amount` DECIMAL(12,2) NOT NULL DEFAULT '0' AFTER `admin_fee_percent`;";
        DB::statement( $net_payable_amount );

        $memberPayouts=MemberPayout::all();

        foreach ($memberPayouts as $payout) {
            $payout->tds_percent=5;
            $payout->admin_fee_percent=0;
            $payout->net_payable_amount=$payout->tds+$payout->payout_amount;
            $payout->save();
        }
    }

    public function memberPayoutIncomeMigration(){

        $tds_fields="ALTER TABLE `member_payout_incomes` CHANGE `tds` `tds` DECIMAL(12,2) NOT NULL DEFAULT '0.000000', CHANGE `admin_fee` `admin_fee` DECIMAL(12,2) NOT NULL DEFAULT '0.000000';";
        DB::statement( $tds_fields );


        $member_payout_id="ALTER TABLE `member_payout_incomes` ADD `member_payout_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `member_id`;";
        DB::statement( $member_payout_id );
        
        $tds_percent="ALTER TABLE `member_payout_incomes` ADD `tds_percent` DECIMAL(5,2) NOT NULL DEFAULT '0' AFTER `tds`;";
        DB::statement( $tds_percent );

        $admin_fee_percent="ALTER TABLE `member_payout_incomes` ADD `admin_fee_percent` DECIMAL(5,2) NOT NULL AFTER `admin_fee`;";
        DB::statement( $admin_fee_percent );

        $net_payable_amount="ALTER TABLE `member_payout_incomes` ADD `net_payable_amount` DECIMAL(12,2) NOT NULL DEFAULT '0' AFTER `admin_fee_percent`;";
        DB::statement( $net_payable_amount );

        $memberPayoutIncomes=MemberPayoutIncome::all();

        foreach ($memberPayoutIncomes as $payout) {
            $memberPayout=MemberPayout::where('payout_id',$payout->payout_id)->where('member_id',$payout->member_id)->first();

            $payout->member_payout_id=$memberPayout->id;
            $payout->tds_percent=5;
            $payout->admin_fee_percent=0;
            $payout->net_payable_amount=$payout->tds+$payout->payout_amount;
            $payout->save();
        }
    }

    public function payoutIncomeMigration(){

        $payout_amount="ALTER TABLE `payout_incomes` CHANGE `payout_amount` `payout_amount` DECIMAL(12,2) NOT NULL DEFAULT '0.000000';";
        DB::statement( $payout_amount );

        $tds_percent="ALTER TABLE `payout_incomes` ADD `tds` DECIMAL(12,2) NOT NULL DEFAULT '0' AFTER `payout_amount`;";
        DB::statement( $tds_percent );

        $tds="ALTER TABLE `payout_incomes` ADD `tds_percent` DECIMAL(5,2) NOT NULL DEFAULT '0' AFTER `tds`;";
        DB::statement( $tds );

        $admin_fee="ALTER TABLE `payout_incomes` ADD `admin_fee` DECIMAL(12,2) NOT NULL AFTER `tds`;";
        DB::statement( $admin_fee );

        $admin_fee_percent="ALTER TABLE `payout_incomes` ADD `admin_fee_percent` DECIMAL(5,2) NOT NULL AFTER `admin_fee`;";
        DB::statement( $admin_fee_percent );

        $net_payable_amount="ALTER TABLE `payout_incomes` ADD `net_payable_amount` DECIMAL(12,2) NOT NULL DEFAULT '0' AFTER `admin_fee_percent`;";
        DB::statement( $net_payable_amount );

        $PayoutIncomes=PayoutIncome::all();

        foreach ($PayoutIncomes as $payout) {
            $PayoutSum=MemberPayoutIncome::select([DB::raw('sum(payout_amount) as total_payout_amount'),DB::raw('sum(tds) as total_tds'),DB::raw('sum(net_payable_amount) as total_net_payable_amount')])->where('payout_id',$payout->payout_id)->where('income_id',$payout->income_id)->first();


            $payout->tds=$PayoutSum->total_tds;
            $payout->net_payable_amount=$PayoutSum->total_net_payable_amount;
            $payout->tds_percent=5;
            $payout->admin_fee_percent=0;
            $payout->net_payable_amount=$payout->tds+$payout->payout_amount;
            $payout->save();
        }
    }

    public function verify(){
        $Payout=MemberPayout::where('payout_id',9)->where('member_id',3)->sum('tds');
        $PayoutIncome=MemberPayoutIncome::where('payout_id',9)->where('member_id',3)->sum('tds');

        \Log::info('Payout - '.$Payout.', Payout Income - '.$PayoutIncome);
    }
    
}
