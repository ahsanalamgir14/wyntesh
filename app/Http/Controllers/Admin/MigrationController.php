<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

use App\Models\Admin\User;
use App\Models\Admin\Member;
use App\Models\Admin\Product;
use App\Models\Admin\MemberIncomeHolding;
use App\Models\Admin\ProductImage;
use App\Models\Admin\ProductVariant;
use App\Models\Admin\MemberPayoutIncome;
use App\Models\User\Order;
use Illuminate\Support\Facades\Storage;

class MigrationController extends Controller
{    

    public function doMigration(){
        $this->luxuryBalanceMigration();
        $this->luxuryIncomeMigration();
       
    }

    public function luxuryIncomeMigration(){
        $luxuryIncomes=MemberPayoutIncome::where('income_id',5)->where('payout_id',20)->get();

        foreach ($luxuryIncomes as $payout) {
            $payout->member->income_wallet_balance-=$payout->net_payable_amount;
            $payout->member->luxury_wallet_balance+=$payout->net_payable_amount;
            $payout->member->save();
        }
    }

    public function luxuryBalanceMigration(){
        $MemberIncomeHolding=MemberIncomeHolding::where('is_paid',0)->get();

        foreach ($MemberIncomeHolding as $holding) {
            $holding->member->luxury_wallet_balance+=$holding->amount;
            $holding->member->save();
            $holding->is_paid=1;
            $holding->save();
        }
    }

    
    
}
