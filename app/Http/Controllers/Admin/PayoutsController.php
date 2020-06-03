<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\Payout;
use App\Models\Admin\PayoutType;
use App\Models\Admin\PayoutIncome;
use App\Events\GeneratePayoutEvent;

class PayoutsController extends Controller
{    

    
    public function getPayouts(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if(!$search){
            $Payout = Payout::with('payout_type')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Payout=Payout::select();
            
            $Payout=$Payout->with('payout_type')->orderBy('id',$sort)->paginate($limit);
        }
   
        $response = array('status' => true,'message'=>"Payout Types retrieved.",'data'=>$Payout);
        return response()->json($response, 200);
    }
  

    public function generateManualPayout(Request $request){
        
        $PayoutType=PayoutType::where('name','Manual')->first();


        $validate = Validator::make($request->all(), [
            'date_range' => 'required',
            'incomes' => 'required',
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $date_range=$request->date_range;
        
        $Payout=new Payout;
        $Payout->payout_type_id=$PayoutType->id;
        $Payout->is_run_by_system=0;
        $Payout->sales_start_date=$request->date_range[0];
        $Payout->sales_end_date=$request->date_range[1];
        $Payout->sales_bv=0;
        $Payout->sales_amount=0;
        $Payout->total_payout=0;
        $Payout->save();

        foreach ($request->incomes as $income_id) {
            $PayoutIncome=new PayoutIncome;
            $PayoutIncome->payout_id=$Payout->id;
            $PayoutIncome->income_id=$income_id;
            $PayoutIncome->payout_amount=0;
            $PayoutIncome->save();
        }

        event(new GeneratePayoutEvent($Payout));

        $response = array('status' => true,'message'=>'Payout Generation added to queue.');
        return response()->json($response, 200);
    }
}
