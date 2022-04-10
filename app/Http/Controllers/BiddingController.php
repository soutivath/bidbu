<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Buddhist;
class BiddingController extends Controller
{
    public function __construct(){

    }

    public function removeLastedBidItem(Request $request, $buddhist_id){

        /**
         * remove lasted index of firebase item
         * --in table buddhist 
         * change highest price 
         * 
         */
        $request->validate([

        ]);
        $isItemBelongToUser = Buddhist::where([
            ["user_id",Auth::id()],
            ["id",$buddhist_id]
        ])->first();
        if(!$isItemBelongToUser){
            return response()->json([
                "data"=>[],
                "message"=>"ທ່ານບໍ່ໄດ້ເປັນເຈົ້າຂອງ Item ນີ້"
            ],400);
        }


    }
}
