<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Constants\Phone;
use App\Http\Resources\BuddhistResource;
use App\Models\Buddhist;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use App\Constants\QueryConstant;
class ShowItemSectionController extends Controller
{
    public function __construct(){

    }

    public function kongDeeCenter(Request $request){
       
        $kongDeeCenterPhoneNumber = Phone::KONGDEE_CENTER_PHONE_NUMBER;
     
        $perPage = QueryConstant::PERPAGE_PAGINATE_DEFAULT;
       
        if($request->has("perPage")){
            $convertedPerPage = (int)$request->perPage;
           


            if($convertedPerPage!=0){
                $perPage = (int)$request->perPage;
            }

            if($convertedPerPage>50){
                $perPage = (int)$request->perPage;
            }
        }
      
      

   

        $items = Buddhist::whereHas("user",function ($query) use ($kongDeeCenterPhoneNumber){
            $loop = 0;
            foreach($kongDeeCenterPhoneNumber as $phone_number){
                if($loop==0){
                    $query->where("phone_number",$phone_number);
                }
                else{
                    $query->orWhere("phone_number",$phone_number);
                }
                $loop++;
                
            }
        })->where([['end_time', '>', Carbon::now()], ["active", "1"]])->with(["type"])->paginate($perPage);
       

        return BuddhistResource::collection($items); 
      
    }
}
