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
use App\Models\Admin\PayoutIncome;
use App\Models\Admin\MemberLevelPayout;
use App\Models\Admin\MembersLegPv;
use App\Models\Admin\MemberMonthlyLegPv;
use App\Models\User\User;
use App\Models\Admin\WalletTransaction;
use App\Models\Superadmin\TransactionType;
use App\Models\Admin\Payout;
use App\Events\GeneratePayoutEvent;
use App\Models\Admin\MemberIncomeHolding;
use Illuminate\Support\Facades\Storage;

class CronsController extends Controller
{    

    public function backupDatabase(){
        $filedata = \Artisan::call('backup:run', [
            '--only-db' => 'default'
        ]);

        $folder =  str_replace(" ","-",env('APP_NAME'));
        $allFiles = Storage::disk('local')->allFiles($folder);
        $filename =Storage::url($allFiles[0]);          

        $file = Storage::disk('local')->get($allFiles[0]);
        $project_directory=env('DO_STORE_PATH');
        $store=Storage::disk('spaces')->put($project_directory.'/backup/'.$filename,$file);
        $url=Storage::disk('spaces')->url($project_directory.'/backup/'.$filename);
        $cdn_url=str_replace('digitaloceanspaces','cdn.digitaloceanspaces', $url);
        Storage::delete(Storage::files($folder));
    }
    public function generateMonthlyPayout(){
      $dt = Carbon::now();
      $day=$dt->day;
      $from='';
      $to='';
      
    if($day=='6'){
        $from=$dt->year.'-'.$dt->month.'-'.'1';
        $from=$dt->year.'-'.$dt->month.'-'.'6';
        $incomes=Income::where('code','SQUAD')->get();
        $PayoutType=PayoutType::where('name','Weekly')->first();
    }else if($day=='12'){
        $from=$dt->year.'-'.$dt->month.'-'.'7';
        $from=$dt->year.'-'.$dt->month.'-'.'12';
        $incomes=Income::where('code','SQUAD')->get();
        $PayoutType=PayoutType::where('name','Weekly')->first();
    }else if($day=='18'){
        $from=$dt->year.'-'.$dt->month.'-'.'13';
        $from=$dt->year.'-'.$dt->month.'-'.'18';
        $incomes=Income::where('code','SQUAD')->get();
        $PayoutType=PayoutType::where('name','Weekly')->first();
    }else if($day=='24'){
        $from=$dt->year.'-'.$dt->month.'-'.'19';
        $from=$dt->year.'-'.$dt->month.'-'.'24';
        $incomes=Income::where('code','SQUAD')->get();
        $PayoutType=PayoutType::where('name','Weekly')->first();
    }else if($day==intval(date('t'))){
        $from=$dt->year.'-'.$dt->month.'-'.'25';
        $from=$dt->year.'-'.$dt->month.'-'.date('t');
        $incomes=Income::all();
        $PayoutType=PayoutType::where('name','Monthly')->first();
    }

    $Payout=new Payout;
    $Payout->payout_type_id=$PayoutType->id;
    $Payout->is_run_by_system=1;
    $Payout->sales_start_date=$from;
    $Payout->sales_end_date=$to;
    $Payout->sales_bv=0;
    $Payout->tds=0;
    $Payout->sales_amount=0;
    $Payout->total_payout=0;
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

public function PVImport(){
    $Members=Member::orderBy('level','desc')->get();

    foreach ($Members as $Member) {
        $data=DB::table('Sheet1')->where('member_id',$Member->user->member_id)->first();

        if($data){

         $member_total_bv=$data->june;

         $path=$Member->path;
         $position=$Member->position;

         $uplines=explode('/', $path);        
         $uplines=array_reverse($uplines);
         $uplines=array_filter($uplines, 'strlen');            
         array_shift($uplines);

         $year=date('2020');
         $month=date('06');

         foreach ($uplines as $upline) {
            $MembersLegPv=MemberMonthlyLegPv::where('member_id',$upline)->where('position',$position)
            ->whereYear('created_at', '=', $year)
            ->whereMonth('created_at', '=', $month)
            ->first();
            $UplineMember=Member::where('id',$upline)->first();

            if($MembersLegPv){                    
                        //$MembersLegPv->current_pv+=$member_total_bv;
                $MembersLegPv->pv+=$member_total_bv;
                $MembersLegPv->save();
            }else{
                $MembersLegPv=new MemberMonthlyLegPv;
                $MembersLegPv->member_id=$upline;
                $MembersLegPv->position=$position;
                        //$MembersLegPv->current_pv=$member_total_bv;
                $MembersLegPv->pv=$member_total_bv;
                $MembersLegPv->created_at='2020-06-05';
                $MembersLegPv->save();
            }
            $position=$UplineMember->position;
        } 
    }

}

}

}
