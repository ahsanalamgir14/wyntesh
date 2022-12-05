<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\Admin\Member;
use App\Models\Admin\Income;
use App\Models\Admin\Sale;
use App\Models\Admin\IncomeParameter;
use App\Models\Admin\PayoutType;
use App\Models\Admin\Backup;
use App\Models\Admin\PayoutIncome;
use App\Models\Admin\MemberLevelPayout;
use App\Models\Admin\MembersLegPv;
use App\Models\Admin\MemberPayout;
use App\Models\Admin\MemberMonthlyLegPv;
use App\Models\Admin\WallOfWyntash;
use App\Models\User\User;
use App\Models\Admin\WalletTransaction;
use App\Models\Superadmin\TransactionType;
use App\Models\Admin\Payout;
use App\Events\GeneratePayoutEvent;
use App\Models\Admin\MemberIncomeHolding;
use App\Models\Admin\IncomeWalletTransactions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CronsController extends Controller
{    

    public function WallOfWyntashReport(){

        $start = new Carbon('first day of last month');
        $start = $start->startOfMonth()->format('Y-m-d'); 

        $last = new Carbon('last day of last month');
        $last = $last->endOfMonth()->format('Y-m-d'); 

        $results=MemberPayout::select('*')->addSelect([DB::raw('sum(payout_amount) as total_payout_amount'),DB::raw('sum(tds) as total_tds'),DB::raw('sum(net_payable_amount) as total_net_payable_amount')])->whereDate('created_at','>=',$start)->where('created_at','<=',$last)->where('payout_amount','>',0)->groupBy('member_id')->get();

        
        WallOfWyntash::truncate();
        
        foreach($results as $data){           

            $bday = new \DateTime($data->member->user->dob);
            $today = new \Datetime(date('y-m-d'));
            $diff = $today->diff($bday);
            
            if($data->username == '142040'){
                continue;
            }
            // if($data->total_amt>=10000){
                $WallOfWyntash = new WallOfWyntash();
                $WallOfWyntash->name = $data->member->user->name;
                $WallOfWyntash->username = $data->member->user->username;
                $WallOfWyntash->age = $diff->y;
                $WallOfWyntash->city = $data->city;
                $WallOfWyntash->profile_picture = $data->member->user->profile_picture;
                $WallOfWyntash->total_amount = $data->total_payout_amount;
                $WallOfWyntash->save();
            // }
        }

    }
    
    public function backupDatabase(){
        $folder     =   str_replace(" ","-",env('APP_NAME'));
        $backup = Backup::whereDate('created_at','<=',Carbon::now()->subDays(7))->get();
        foreach ($backup as $key => $value) {
            Storage::disk('s3')->delete($value->path);
            $temp  = Backup::find($value->id);
            $temp->delete();
        }
     
        $filedata = \Artisan::call('backup:run', [
            '--only-db' => 'default'
        ]);

        $allFiles   =   Storage::disk('local')->allFiles($folder);
        $filename   =   Storage::url($allFiles[0]);    

        $timeStamp=str_replace(':','-',str_replace(' ','-',Carbon::now()));

        $file       =   Storage::disk('local')->get($allFiles[0]);  // full zip file
        $filename   =   Storage::files($folder)[0];
        $filename   = 'backup/'.env('APP_ENV').'-'.rand(10,100000).'-'.$timeStamp;
       // $filename   =   str_replace(env('APP_NAME').'/','backup/', $filename);
   
        $project_directory=env('DO_STORE_PATH');
        $store=Storage::disk('s3')->put($project_directory.'/'.$filename,$file);
        Storage::disk('spaces')->put($project_directory.'/'.$filename,$file);
        $url=Storage::disk('s3')->url($project_directory.'/'.$filename);
        
        $backup = new Backup;
        $backup->path = env('DO_STORE_PATH').'/'.$filename;
        $backup->url = $url;
        $backup->size = Storage::size($allFiles[0]);
        $backup->save();
        Storage::delete(Storage::files($folder));
    }

    public function releaseHoldPayout(){
        
        $TransactionType=TransactionType::where('name','Credit')->first();

        $squad_members=Member::where('rank_id',8)->get();

        foreach ($squad_members as $member) {
            $MemberIncomeHolding=MemberIncomeHolding::selectRaw('*, sum(amount) as withhold_amount')
           ->where('member_id',$member->id)->where('is_paid',0)->first();

            if($MemberIncomeHolding->withhold_amount && $TransactionType){
                $IncomeWalletTransactions=new IncomeWalletTransactions;
                $IncomeWalletTransactions->member_id=$member->id;
                $IncomeWalletTransactions->amount=$MemberIncomeHolding->withhold_amount;
                $IncomeWalletTransactions->balance=$MemberIncomeHolding->withhold_amount+$member->wallet_balance;
                $IncomeWalletTransactions->transaction_type_id=$TransactionType->id;
                $IncomeWalletTransactions->transfered_to=$member->user->id;
                $IncomeWalletTransactions->note='Withhold Payout';
                $IncomeWalletTransactions->save(); 

                $member->income_wallet_balance+=$MemberIncomeHolding->withhold_amount;
                $member->save();

                MemberIncomeHolding::where('member_id',$member->id)->where('is_paid',0)->update(['is_paid'=>1,'paid_at'=>Carbon::now()]);
            }
        }

        
    }

    public function generateMonthlyPayout()
    {
        $dt = Carbon::now();
        $day=$dt->day;
        $from='';
        $to='';
        $PayoutType='';

        // if($day=='4'){
        //     $from=$dt->year.'-'.$dt->month.'-'.'1';
        //     $to=$dt->year.'-'.$dt->month.'-'.'3';
        //     $incomes=Income::whereIn('code',['REWARD','AFFILIATE','SQUAD','ELEVATION','LUXURY'])->get();
        //     $PayoutType=PayoutType::where('name','Weekly')->first();
        // }else if($day=='7'){
        //     $from=$dt->year.'-'.$dt->month.'-'.'4';
        //     $to=$dt->year.'-'.$dt->month.'-'.'6';
        //     $incomes=Income::whereIn('code',['REWARD','AFFILIATE','SQUAD','ELEVATION','LUXURY'])->get();
        //     $PayoutType=PayoutType::where('name','Weekly')->first();
        // }else if($day=='10'){
        //     $from=$dt->year.'-'.$dt->month.'-'.'7';
        //     $to=$dt->year.'-'.$dt->month.'-'.'9';
        //     $incomes=Income::whereIn('code',['REWARD','AFFILIATE','SQUAD','ELEVATION','LUXURY'])->get();
        //     $PayoutType=PayoutType::where('name','Weekly')->first();
        // }else if($day=='13'){
        //     $from=$dt->year.'-'.$dt->month.'-'.'10';
        //     $to=$dt->year.'-'.$dt->month.'-'.'12';
        //     $incomes=Income::whereIn('code',['REWARD','AFFILIATE','SQUAD','ELEVATION','LUXURY'])->get();
        //     $PayoutType=PayoutType::where('name','Weekly')->first();
        // }else if($day=='16'){
        //     $from=$dt->year.'-'.$dt->month.'-'.'13';
        //     $to=$dt->year.'-'.$dt->month.'-'.'15';
        //     $incomes=Income::whereIn('code',['REWARD','AFFILIATE','SQUAD','ELEVATION','LUXURY'])->get();
        //     $PayoutType=PayoutType::where('name','Weekly')->first();
        // }else if($day=='19'){
        //     $from=$dt->year.'-'.$dt->month.'-'.'16';
        //     $to=$dt->year.'-'.$dt->month.'-'.'18';
        //     $incomes=Income::whereIn('code',['REWARD','AFFILIATE','SQUAD','ELEVATION','LUXURY'])->get();
        //     $PayoutType=PayoutType::where('name','Weekly')->first();
        // }else if($day=='22'){
        //     $from=$dt->year.'-'.$dt->month.'-'.'19';
        //     $to=$dt->year.'-'.$dt->month.'-'.'21';
        //     $incomes=Income::whereIn('code',['REWARD','AFFILIATE','SQUAD','ELEVATION','LUXURY'])->get();
        //     $PayoutType=PayoutType::where('name','Weekly')->first();
        // }else if($day=='25'){
        //     $from=$dt->year.'-'.$dt->month.'-'.'22';
        //     $to=$dt->year.'-'.$dt->month.'-'.'24';
        //     $incomes=Income::whereIn('code',['REWARD','AFFILIATE','SQUAD','ELEVATION','LUXURY'])->get();
        //     $PayoutType=PayoutType::where('name','Weekly')->first();
        // }else if($day=='28'){
        //     $from=$dt->year.'-'.$dt->month.'-'.'25';
        //     $to=$dt->year.'-'.$dt->month.'-'.'27';
        //     $incomes=Income::whereIn('code',['REWARD','AFFILIATE','SQUAD','ELEVATION','LUXURY'])->get();
        //     $PayoutType=PayoutType::where('name','Weekly')->first();
        // }else 
        if($day=='1'){
            $dt->modify('-1 months');
            $from=$dt->year.'-'.$dt->month.'-'.'28';
            $to= $dt->endOfMonth()->toDateString('Y-m-d');
            $incomes=Income::all();
            $PayoutType=PayoutType::where('name','Monthly')->first();
        }

        if(!$PayoutType)
            return;

        $Payout=new Payout;
        $Payout->payout_type_id=$PayoutType->id;
        $Payout->is_run_by_system=1;
        $Payout->sales_start_date=$from;
        $Payout->sales_end_date=$to;
        $Payout->sales_bv=0;
        $Payout->tds=0;
        $Payout->sales_amount=0;
        $Payout->payout_amount=0;
        $Payout->started_at=Carbon::now();
        $Payout->save();

        foreach ($incomes as $income) {
            $PayoutIncome=new PayoutIncome;
            $PayoutIncome->payout_id=$Payout->id;
            $PayoutIncome->income_id=$income->id;
            $PayoutIncome->payout_amount=0;
            $PayoutIncome->save();
        }

        event(new GeneratePayoutEvent($Payout));

    }

    public function generateWeeklyPayout(){
        
        $date = Carbon::now()->subDays(7);
        $from=date("Y-m-d", strtotime('saturday this week', strtotime($date)));   
        $dt = Carbon::now()->subDays(1);
        $to=date("Y-m-d", strtotime('friday this week', strtotime($dt)));

        $incomes=Income::whereIn('code',['REWARD','AFFILIATE','SQUAD','ELEVATION','LUXURY'])->get();
        $PayoutType=PayoutType::where('name','Weekly')->first();
       
        
        $Payout=new Payout;
        $Payout->payout_type_id=$PayoutType->id;
        $Payout->is_run_by_system=1;
        $Payout->sales_start_date=$from;
        $Payout->sales_end_date=$to;
        $Payout->sales_bv=0;
        $Payout->tds=0;
        $Payout->sales_amount=0;
        $Payout->payout_amount=0;
        $Payout->started_at=Carbon::now();
        $Payout->save();

        foreach ($incomes as $income) {
            $PayoutIncome=new PayoutIncome;
            $PayoutIncome->payout_id=$Payout->id;
            $PayoutIncome->income_id=$income->id;
            $PayoutIncome->payout_amount=0;
            $PayoutIncome->save();
        }

        event(new GeneratePayoutEvent($Payout));
    }

}
