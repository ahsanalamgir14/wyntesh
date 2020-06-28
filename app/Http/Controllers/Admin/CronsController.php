<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

use App\Models\Admin\Member;
use App\Models\Admin\Income;
use App\Models\Admin\IncomeParameter;
use App\Models\Admin\PayoutType;
use App\Models\Admin\PayoutIncome;
use App\Models\Admin\MemberLevelPayout;
use App\Models\User\User;
use App\Models\Admin\WalletTransaction;
use App\Models\Superadmin\TransactionType;
use App\Models\Admin\Payout;
use App\Events\GenerateMonthlyPayoutEvent;
use App\Models\Admin\MemberIncomeHolding;

class CronsController extends Controller
{    

    public function delete3MonthHoldIncome()
    {
        $days_90_before_date=Carbon::now()->subDays(90)->toDateString('Y-m-d');
        $MemberIncomeHoldings=MemberIncomeHolding::whereDate('created_at', '<=', $days_90_before_date)->where('is_paid',0)->get();

        foreach ($MemberIncomeHoldings as $Holding) {           
           $Holding->delete();
        }
    }

    public function generateMonthlyPayout(){
      $dt = Carbon::now();
      $dt->modify('-2 months');
      $from= $dt->firstOfMonth()->toDateString('Y-m-d');
      $to= $dt->endOfMonth()->toDateString('Y-m-d');
      $incomes=Income::all();

      $PayoutType=PayoutType::where('name','Monthly')->first();

        $Payout=new Payout;
        $Payout->payout_type_id=$PayoutType->id;
        $Payout->is_run_by_system=1;
        $Payout->sales_start_date=$from;
        $Payout->sales_end_date=$to;
        $Payout->sales_bv=0;
        $Payout->tds=0;
        $Payout->sales_amount=0;
        $Payout->total_payout=0;
        $Payout->save();

        foreach ($incomes as $income) {
            $PayoutIncome=new PayoutIncome;
            $PayoutIncome->payout_id=$Payout->id;
            $PayoutIncome->income_id=$income->id;
            $PayoutIncome->payout_amount=0;
            $PayoutIncome->tds=0;
            $PayoutIncome->save();
        }

        event(new GenerateMonthlyPayoutEvent($Payout));

    }


}
