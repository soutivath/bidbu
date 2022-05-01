<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Buddhist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Database\Transaction;
use Kreait\Firebase\Exception\Database\TransactionFailed;
use App\Models\NotificationFirebase;
use Carbon\Carbon;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class BiddingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('isUserActive:api');
    }

    public function removeLastedBidItem(Request $request, $buddhist_id)
    {
      
        $request->validate([
            "price"=>"required|integer",
            "id"=>"required|integer|exists:users,id",
            "firebase_uid"=>"required|string|exists:users,firebase_uid",
        ]);
      
    
        $messaging = app('firebase.messaging');
        $androidConfig = AndroidConfig::fromArray([
            'ttl' => '3600s',
            'priority' => 'high',

        ]);
      
     

        DB::beginTransaction();

        try {
            $isItemExisting = Buddhist::findOrFail($buddhist_id);
          
            if (!$isItemExisting) {
                return response()->json([
                    "data" => [],
                    "message" => "no data found",
                    "success" => false,
                ], 404);
            }
          

            if (Carbon::now()->greaterThan(Carbon::now()->parse($isItemExisting->end_time))) {
                return response()->json(["message" => "This item is expired."], 400);
            }

            if (Auth::id() != $isItemExisting->user_id) {
                return response()->json([
                    "data" => [],
                    "message" => "You don't have permission to access this item",
                    "success" => false,
                ], 403);
            }

            if($isItemExisting->user_id==$isItemExisting->highBidUser){
                return response()->json([
                    "data" => [],
                    "message" => "This is lasted price",
                    "success" => false,
                ], 400);
            }


            $database = app('firebase.database');


            $reference = $database->getReference('buddhist/'.$buddhist_id.'/')
                ->orderByChild('price')
                ->limitToLast(2)
                ->getSnapshot()
                ->getValue();
                
                if($reference==null){
                    return response()->json([
                        "data"=>[],
                        "success" => false,
                        "message" => "No data found"
                    ]);
                }


            $keys   = array_keys($reference);
            $values = array_values($reference);

            $previousUserKey = $keys[0];
            $previousUserValue = $values[0];

           

            $currentUserKey = $keys[1];
            $currentUserValue = $values[1];

            $previousPrice = $previousUserValue["price"];
            $highBidUser = 0;
            $previous_winner_user_id= "";


            if($previousUserValue["uid"]==Auth::user()->firebase_uid){
              
            $highBidUser = Auth::id();
            $previous_winner_user_id= "empty";
            }else{
                $highBidUser = $previousUserValue["id"];
                $previous_winner_user_id= $previousUserValue["uid"];
            }

            //check match uid
            if(!($isItemExisting->winner_user_id==$currentUserValue["uid"]&&$isItemExisting->winner_user_id==$request->firebase_uid)){
                return response()->json([
                    "data" => [],
                    "message" => "UID not match please try again",
                    "success" => false,
                ], 500);
            }

            //check match price
            if(!($isItemExisting->highest_price==$currentUserValue["price"]&&$isItemExisting->highest_price==$request->price)){
                return response()->json([
                    "data" => [],
                    "message" => "Price not match please try again",
                    "success" => false,
                ], 500);
            }

            //check match id
            if(!($isItemExisting->highBidUser==$currentUserValue["id"]&&$isItemExisting->highBidUser==$request->id)){
                return response()->json([
                    "data" => [],
                    "message" => "ID not match please try again",
                    "success" => false,
                ], 500);
            }

           
         
         

            $isItemExisting->highest_price =(int) $previousPrice;
            $isItemExisting->winner_fcm_token = "rollback";
            $isItemExisting->highBidUser = (int)$highBidUser;
            $isItemExisting->winner_user_id = $previous_winner_user_id;
            $isItemExisting->save();


            NotificationFirebase::where([
                //  ["user_id", "!=", Auth::id()],
                ["buddhist_id", $request->buddhist_id],
            ])->where("notification_type", "bidding_participant")
                ->update([
                    //'notification_type' => "bidding_participant",
                    'notification_time' => date('Y-m-d H:i:s'),
                    'data' => $previousUserValue["price"],
                    'read' => 0,
                ]);
            //delete current user from firebase

            try {
                $currentUserToBeDeleted = $database->getReference("buddhist/".$buddhist_id."/" . $currentUserKey);
                $database->runTransaction(function (Transaction $transaction) use ($currentUserToBeDeleted) {
                    $transaction->snapshot($currentUserToBeDeleted);
                    $transaction->remove($currentUserToBeDeleted);
                });
            } catch (TransactionFailed $e) {
                DB::rollBack();
                return response()->json([
                    "data" => [],
                    "message" => "Something went wrong",
                    "success" => false,
                ], 500);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    "data" => [],
                    "message" => "Something went wrong",
                    "success" => false,
                ], 500);
            }

            $message = CloudMessage::withTarget("topic", $isItemExisting->topic)
                ->withNotification(Notification::fromArray([
                    'title' => 'ຈາກ '.$isItemExisting->name.' ທີ່ທ່ານໄດ້ຮ່ວມກະມູນ',
                    'body' => 'ເຈົ້າຂອງສິນຄ້າຍົກເລີກການປະມູນຂອງ '.$currentUserValue["name"].' ຕອນນີ້ລາຄາຢູ່ທີ່ '.$previousUserValue["price"],
                    'image' => \public_path("/notification_images/chat.png"),
                ]))
                ->withData([
                    'buddhist_id' => $isItemExisting->id,
                    'type' => 'remove_bidding',
                    'sender' => "0",
                    'result' => "",
                ]);
            $message = $message->withAndroidConfig($androidConfig);
            $messaging->send($message);
            DB::commit();
            return response()->json([
                "data" => [],
                "message" => "Lasted bid was removed successfully",
                "success" => true,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
