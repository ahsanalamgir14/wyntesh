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
use App\Models\Admin\MemberMonthlyLegPv;
use App\Models\Admin\WallOfWyntash;
use App\Models\User\User;
use App\Models\Admin\WalletTransaction;
use App\Models\Superadmin\TransactionType;
use App\Models\Admin\Payout;
use App\Events\GeneratePayoutEvent;
use App\Models\Admin\MemberIncomeHolding;
use App\Models\Admin\IncomeWalletTransactions;
use Illuminate\Support\Facades\Storage;

class CronsController extends Controller
{    

    public function WallOfWyntashReport(){

        $start = new Carbon('first day of last month');
        $start = $start->startOfMonth()->format('Y-m-d'); 

        $last = new Carbon('last day of last month');
        $last = $last->endOfMonth()->format('Y-m-d'); 

        $results = DB::select(DB::raw("SELECT *,sum(amt) total_amt from (SELECT  ab.created_at ,u.profile_picture, u.name , u.username, u.dob , k.city , ab.member_id,sum(amount) as amt FROM `affiliate_bonus` as ab right join `members` as m on m.id = ab.member_id right join `users` as u on u.id = m.user_id  right join kyc as k on k.member_id = m.id where date(ab.created_at) >= '$start' and  date(ab.created_at) <= '$last' group by ab.member_id UNION SELECT tp.created_at ,u.profile_picture, u.name ,u.username,u.dob ,k.city,tp.member_id,sum(total_payout+tds) as amt FROM `member_payouts` as tp left join `members` as m on m.id = tp.member_id left join `users` as u on u.id = m.user_id left join kyc as k on k.member_id = m.id where date(tp.created_at) >= '$start' and  date(tp.created_at) <= '$last' group by member_id UNION SELECT r.created_at,u.profile_picture, u.name, u.username, u.dob, k.city, r.member_id, SUM(amount) AS amt FROM `rewards` AS r LEFT JOIN `members` AS m ON m.id = r.member_id LEFT JOIN `users` AS u ON u.id = m.user_id LEFT JOIN kyc AS k ON k.member_id = m.id where date(r.created_at) >= '$start' and  date(r.created_at) <= '$last' GROUP BY member_id) tmp group by tmp.member_id order by total_amt desc ") );
        
        WallOfWyntash::truncate();
        
        foreach($results as $data){           

            $bday = new \DateTime($data->dob);
            $today = new \Datetime(date('y-m-d'));
            $diff = $today->diff($bday);
            
            if($data->username == '142040'){
                continue;
            }
            // if($data->total_amt>=10000){
                $WallOfWyntash = new WallOfWyntash();
                $WallOfWyntash->name = $data->name;
                $WallOfWyntash->username = $data->username;
                $WallOfWyntash->age = $diff->y;
                $WallOfWyntash->city = $data->city;
                $WallOfWyntash->profile_picture = $data->profile_picture;
                $WallOfWyntash->total_amount = $data->total_amt;
                $WallOfWyntash->save();
            // }
        }

    }
    
    public function backupDatabase(){
        $folder     =   str_replace(" ","-",env('APP_NAME'));
        $backup = Backup::whereDate('created_at','<=',Carbon::now()->subDays(7))->get();
        foreach ($backup as $key => $value) {
            Storage::disk('spaces')->delete($value->path);
            $temp  = Backup::find($value->id);
            $temp->delete();
        }
     
        $filedata = \Artisan::call('backup:run', [
            '--only-db' => 'default'
        ]);

       
        $allFiles   =   Storage::disk('local')->allFiles($folder);
        $filename   =   Storage::url($allFiles[0]);    

        $file       =   Storage::disk('local')->get($allFiles[0]);  // full zip file
        $filename   =   Storage::files($folder)[0];
        $filename   =   str_replace(env('APP_NAME').'/','backup/', $filename);
   
        $project_directory=env('DO_STORE_PATH');
        $store=Storage::disk('spaces')->put($project_directory.'/'.$filename,$file);
        $url=Storage::disk('spaces')->url($project_directory.'/'.$filename);
        $cdn_url=str_replace('digitaloceanspaces','cdn.digitaloceanspaces', $url);

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
        //$dt->modify('-1 months');
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
