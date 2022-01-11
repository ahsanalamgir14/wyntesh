<?php

namespace App\Http\Controllers\User;

use App\Models\Admin\Combo;
use Illuminate\Http\Request;
use App\Models\Admin\Product;
use App\Models\Admin\ComboCategory;
use App\Http\Controllers\Controller;
use App\Models\Admin\ProductVariant;

class CombosController extends Controller
{
    public function getSingleCombo($id)
    {
        $Combo=Combo::with('categories','categories.category')->find($id);
        
        $ComboCategory=ComboCategory::where('combo_id',$Combo->id)->pluck('category_id')->toArray();
         if($Combo){
            $Products=Product::whereHas('categories', function($q)use($ComboCategory){
                $q->whereIn('categories.id',$ComboCategory);
            })->with('categories','images.variant.color','images.variant.size','variants.color','variants.size')->where('is_active',1)->get();  

            
            $response = array('status' => true,'message'=>'Combo retrieved.','data'=>$Combo,'products'=>$Products);             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Combo not found');
            return response()->json($response, 404);
        }
    }
}
