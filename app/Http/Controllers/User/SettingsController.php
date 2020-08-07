<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\CompanySetting;
use App\Models\Admin\Setting;
use App\Models\Admin\Member;
use App\Models\Admin\WalletTransaction;
use App\Models\Admin\AffliateBonus;
use App\Models\User\Order;

class SettingsController extends Controller
{

    public function deductTds(){ 
        // $wallets = WalletTransaction::whereIn('transaction_type_id',[9])->where('member_id',101)->get();
          $orders = Order::whereDate('created_at','>=','2020-08-05')->whereDate('created_at','<=','2020-08-07')->get();
        // foreach($wallets as $wallet){

        //     $tds = ($wallet->amount)*5/100;
        //     $newamount  = $wallet->amount-$tds;
        //     $wallet->amount = $newamount;
        //     $wallet->save();

        //     if($wallet->transaction_type_id == 9){
        //         $wallet->member->wallet_balance -= $tds;
        //         $wallet->member->save();
        //     }
        // }
        foreach($orders as $Order){

            $tds_percentage = CompanySetting::getValue('tds_percentage');
            $amount = ($Order->pv*20)/100; 
            $tds=($amount*$tds_percentage)/100;

            //$deducted_tds_amount = $amount-($amount*$tds_percentage)/100;

            $Order->user->member->sponsor->wallet_balance -= $tds;
            $Order->user->member->sponsor->save();
            
        }
    }


    
    public function getMemberSettings()
    {
        $settings= CompanySetting::
                    pluck('value', 'key')
                    ->toArray();
        $response = array('status' => true,'message'=>'Settings retrived.','data'=>$settings);             
        return response()->json($response, 200);
    }

    public function getCopanyDetailsSettings()
    {
        $settings= Setting::where('is_public',1)
        ->get()->pluck('value', 'key')->toArray();
        $response = array('status' => true,'message'=>'Settings retrived.','data'=>$settings);             
        return response()->json($response, 200);
    }
}
