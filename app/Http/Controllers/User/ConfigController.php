<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Superadmin\PaymentMode;
use App\Models\Superadmin\TransactionType;
use App\Models\Admin\Package;
use App\Models\Admin\BankPartner;

class ConfigController extends Controller
{    

    public function allTransactionTypes()
    {        
        $TransactionTypes=TransactionType::all();        
        $response = array('status' => true,'message'=>"Transaction types retrieved.",'data'=>$TransactionTypes);
        return response()->json($response, 200);
    }

    public function allPaymentModes()
    {        
        $PaymentModes=PaymentMode::all();        
        $response = array('status' => true,'message'=>"Payment modes retrieved.",'data'=>$PaymentModes);
        return response()->json($response, 200);
    }

    public function allPackages()
    {        
        $Packages=Package::all();        
        $response = array('status' => true,'message'=>"Packages retrieved.",'data'=>$Packages);
        return response()->json($response, 200);
    }

    public function allBankPartners()
    {        
        $BankPartners=BankPartner::all();        
        $response = array('status' => true,'message'=>"Bank partners retrieved.",'data'=>$BankPartners);
        return response()->json($response, 200);
    }

}
