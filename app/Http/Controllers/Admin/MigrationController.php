<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

use App\Models\Admin\User;
use App\Models\Admin\Member;
use App\Models\Admin\Product;
use App\Models\Admin\ProductImage;
use App\Models\Admin\ProductVariant;
use App\Models\User\Order;
use Illuminate\Support\Facades\Storage;

class MigrationController extends Controller
{    

    public function doMigration(){
        $this->addNewFields();
        $this->productVarintsAdd();
       
    }

    public function addNewFields(){
        $cost_gst_rate="ALTER TABLE `products` CHANGE `gst_rate` `cost_gst_rate` DECIMAL(12,2) NULL DEFAULT '0.00';";
        DB::statement( $cost_gst_rate );

        $cost_gst_amount="ALTER TABLE `products` CHANGE `cost_gst` `cost_gst_amount` DECIMAL(12,2) NULL DEFAULT '0.00';";
        DB::statement( $cost_gst_amount );

        $cost_amount="ALTER TABLE `products` CHANGE `cost_amount` `cost_amount` DECIMAL(12,2) NULL DEFAULT '0.00';";
        DB::statement( $cost_amount );

        $dp_gst_rate="ALTER TABLE `products` CHANGE `dp_gst_rate` `dp_gst_rate` DECIMAL(12,2) NULL DEFAULT '0.00';";
        DB::statement( $dp_gst_rate );

        $dp_gst_amount="ALTER TABLE `products` CHANGE `dp_gst` `dp_gst_amount` DECIMAL(12,2) NULL DEFAULT '0.00';";
        DB::statement( $dp_gst_amount );

        $dp_amount="ALTER TABLE `products` CHANGE `dp_amount` `dp_amount` DECIMAL(12,2) NULL DEFAULT '0.00';";
        DB::statement( $dp_amount );

        $retail_gst_rate="ALTER TABLE `products` CHANGE `retail_gst_rate` `retail_gst_rate` DECIMAL(12,2) NULL DEFAULT '0.00';";
        DB::statement( $retail_gst_rate );

        $retail_gst_amount="ALTER TABLE `products` CHANGE `retail_gst` `retail_gst_amount` DECIMAL(12,2) NULL DEFAULT '0.00';";
        DB::statement( $retail_gst_amount );

        $retail_amount="ALTER TABLE `products` CHANGE `retail_amount` `retail_amount` DECIMAL(12,2) NULL DEFAULT '0.00';";
        DB::statement( $retail_amount );


        //Users Fields
        $user_fields="ALTER TABLE `users` ADD `country_code` VARCHAR(20) NULL AFTER `otp_valid_till`, ADD `currency_code` VARCHAR(16) NULL AFTER `country_code`;";
        DB::statement( $user_fields );

        $low_stock_count="INSERT INTO `company_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES (NULL, 'low_stock_count', '5', '2021-01-11 13:31:42', NULL);";
        DB::statement( $low_stock_count );

        $cart_variant="ALTER TABLE `cart` ADD `variant_id` INT NOT NULL AFTER `product_id`;";
        DB::statement( $cart_variant );

        $currencies="INSERT INTO `currencies` (`id`, `name`, `code`, `symbol`, `conversion_rate`, `created_at`, `updated_at`, `deleted_at`) VALUES
        (1, 'Indian Rupee', 'INR', '₹', '1.0000', '2020-12-14 16:26:07', '2020-12-14 16:26:07', NULL),
        (2, 'US Dollar', 'USD', '$', '74.0000', '2020-12-14 16:26:07', '2020-12-14 16:26:07', NULL),
        (3, 'British Pound', 'GBP', '£', '99.0000', '2020-12-14 16:26:07', '2020-12-14 16:26:07', NULL);";
        DB::statement( $currencies );

        $update_user_currencies="update users set currency_code='INR', country_code='91';";
        DB::statement( $update_user_currencies );

        $shipping_criteria="INSERT INTO `company_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES (NULL, 'shipping_criteria', '500', '2021-01-11 14:42:02', NULL);";
        DB::statement( $shipping_criteria );

        $default_country_code="INSERT INTO `company_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES (NULL, 'default_country_code', '91', '2021-01-11 19:03:23', NULL);";

        DB::statement( $default_country_code );

        $gstin="INSERT INTO `settings` (`id`, `key`, `value`, `is_public`, `created_at`, `updated_at`) VALUES (NULL, 'default_country_code', '91', '1', '2021-01-11 18:50:24', NULL), (NULL, 'gstin', '33AACW6547R1ZM', '1', '2021-01-11 18:50:24', NULL);";
        DB::statement( $gstin );

        $address_update="update addresses set country_code='91', country='India', state='Tamil Nadu'";
        DB::statement( $address_update );

        $kyc_update="update kyc set country_code='91', country='India';";
        DB::statement( $kyc_update );

        $truncat_cart="TRUNCATE cart;"
        DB::statement( $truncat_cart );

        
    }

    public function productVarintsAdd(){
        $products=Product::all();

        foreach ($products as $product) {
            $variant=new ProductVariant;
            $variant->product_id=$product->id;
            $variant->sku_code='SKU_'.str_replace(' ', '', $product->product_number);
            $variant->stock=1;
            $variant->is_active=1;
            $variant->save();
        }
    }

    
    
}
