<?php

namespace App\Http\Controllers;

use App\Http\Resources\buddhistCollection;
use App\Http\Resources\BuddhistResource;
use App\Http\Resources\checkBuddhistResultResource;
use App\Http\Resources\OneBuddhistResource;
use App\Http\Resources\participantBiddingResource;
use App\Models\Buddhist;
use App\Models\NotificationFirebase;
use Auth;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Image;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;

use App\Constants\QueryConstant;
use Exception;

class BuddhistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth:api')->except('index', 'show', 'buddhistType', 'recommendedBuddhist', 'countByFavorite', 'almostEnd');
        $this->middleware('isUserActive:api')->except('index', 'show', 'buddhistType', 'recommendedBuddhist', 'countByFavorite', 'almostEnd');
    }
    public function index(Request $request)
    {
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

        if ($request->input("search")) {
           $bud =  DB::table("buddhists")->leftJoin('verifies',"buddhists.user_id",'=','verifies.user_id')
            ->where([['buddhists.end_time', '>', Carbon::now()], ["buddhists.active", "1"], ["buddhists.name", "LIKE", "%" . $request->input("search") . "%"]])
            ->select(
                'buddhists.id','buddhists.name','buddhists.price','buddhists.highest_price','buddhists.place','buddhists.end_time','buddhists.image_path','verifies.file_verify_status'
            )
            ->orderBy("buddhists.created_at","desc")->paginate($perPage);
            // $bud = Buddhist::where([
            //     ['end_time', '>', Carbon::now()], ["active", "1"], ["name", "LIKE", "%" . $request->input("search") . "%"],
            // ])->with('type')->orderBy("created_at", "desc")->paginate($perPage);
            return BuddhistResource::collection($bud);

        } else {
            $bud =  DB::table("buddhists")->leftJoin('verifies',"buddhists.user_id",'=','verifies.user_id')
            ->where([['buddhists.end_time', '>', Carbon::now()], ["buddhists.active", "1"]])
            ->select(
                'buddhists.id','buddhists.name','buddhists.price','buddhists.highest_price','buddhists.place','buddhists.end_time','buddhists.image_path','verifies.file_verify_status'
            )
            ->orderBy("buddhists.created_at","desc")->paginate($perPage);
           // $bud = Buddhist::where([['end_time', '>', Carbon::now()], ["active", "1"]])->with('type')->orderBy("created_at", "desc")->paginate($perPage);
            return BuddhistResource::collection($bud);

        }

    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|min:3|max:100|string',
            'detail' => 'required|string',
            'end_datetime' => 'required|date|date_format:Y-m-d H:i:s|after:now',
            'price' => 'required|numeric|gt:0',
            'images' => 'required|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,PNG|max:30720',
            'type_id' => 'required|string',

            /*  'pay_choice' => 'required|string',
            'bank_name' => 'sometimes|string',
            'account_name' => 'sometimes|string',
            'account_number' => 'sometimes|string|max:12',

            'sending_choice' => 'required|string',
            'place_send' => 'sometimes|string',
            'tel' => 'sometimes|string',
            'more_info' => 'sometimes|string',*/

            'place' => 'required|string',
            'status' => 'required|string',

            'price_smallest' => 'required|integer',

            'fcm_token' => 'required|string',
            'minimum_price'=>'required|integer'
        ]);

        $checkSameItemInNamePriceDetail = Buddhist::where([
            ["id",Auth::id()],
            ["name"=>$request->name],
            ["detail"=>$request->detail],
            ["price"=>$request->price]
        ])->first();
        if($checkSameItemInNamePriceDetail){
            return response()->json([
                "data"=>[],
                "message"=>"Your item must be in the same",
                "success"=>false
            ],400);
        }
        
        


     
        
        $messaging = app('firebase.messaging');
        $androidConfig = AndroidConfig::fromArray([
            'ttl' => '3600s',
            'priority' => 'high',
        ]);
        $result = $messaging->validateRegistrationTokens($request->fcm_token);
        if ($result['invalid'] != null) {
            return response()->json(['data' => 'your json token is invalid'], 404);
        }

        $bud = new Buddhist();
        $bud->name = $request->name;
        $bud->detail = $request->detail;
        $bud->price = $request->price;
        $bud->start_time = Carbon::now();
        $bud->end_time = $request->end_datetime;
        $bud->highest_price = $request->price;
        //$bud->pay_choice = $request->pay_choice;
        //$bud->sending_choice = $request->sending_choice;
        $bud->place = $request->place;
        $bud->status = $request->status;
        $bud->priceSmallest = $request->price_smallest;

        $bud->minimum_price=$request->minimum_price;
        /* if ($request->has('bank_name')) {
        $bud->bank_name = $request->bank_name;
        }
        if ($request->has('account_name')) {
        $bud->account_name = $request->account_name;
        }
        if ($request->has('account_number')) {
        $bud->account_number = $request->account_number;
        }
        if ($request->has('place_send')) {
        $bud->place_send = $request->place_send;
        }
        if ($request->has('tel')) {
        $bud->tel = $request->tel;
        }
        if ($request->has('more_info')) {
        $bud->more_info = $request->more_info;
        }*/

        $folderName = uniqid() . "_" . time();
        if (!\File::isDirectory(public_path("/buddhist_images"))) {
            \File::makeDirectory(public_path('/buddhist_images'), 493, true);
        }
        if (!\File::isDirectory(public_path("/buddhist_images/" . $folderName))) {
            \File::makeDirectory(public_path('/buddhist_images/' . $folderName), 493, true);
        }

        $bud->image_path = $folderName;
        $bud->topic = "buddhist_topic_" . \uniqid() . "_" . time();
        $bud->comment_topic = "comment_topic" . \time() . "_" . \uniqid();
        $bud->user_id = Auth::id();
        $bud->type_id = $request->type_id;
        $bud->highBidUser = Auth::id();
        $bud->save();

        // $messaging->subscribeToTopic($bud->topic, $request->fcm_token);

        foreach ($request->images as $image) {
            $fileExtension = $image->getClientOriginalExtension();
            $fileName = 'buddhist' . \uniqid() . "_" . time() . '.' . $fileExtension;
            $location = public_path("/buddhist_images/" . $folderName . "/" . $fileName);
            Image::make($image)->resize(800, null, function ($constraint) {$constraint->aspectRatio();})->save($location);

        }
        try {
            $database = app('firebase.database');
            $reference = $database->getReference('buddhist/' . $bud->id . '/')
                ->push([
                    'uid' => Auth::user()->firebase_uid, //owner id
                    'price' => $request->price, // owner start price
                    'name' => Auth::user()->name,
                    'picture' => Auth::user()->getProfilePath(),
                ]);

                $newItemCondition = "'" . \Config::get("values.GLOBAL_BUDDHIST_TOPIC") . "' in topics && !('" . Auth::user()->topic . "' in topics)";
                $new_item_message = CloudMessage::withTarget('condition',$newItemCondition)
                ->withNotification(Notification::fromArray([
                    'title' => '????????????????????????',
                    'body' => '????????????????????????????????? '.$bud->name,
                    'image' => \public_path("/notification_images/chat.png"),

                ]))
                ->withData([
                    'buddhist_id' =>$bud->id,
                    'type' => 'new_item_notification',
                    'sender' => Auth::id(),
                    'result' => "new_item_notification",
                ]);
            $new_item_message = $new_item_message->withAndroidConfig($androidConfig);
            $messaging->send($new_item_message);

            return response()->json(['data' => $bud], 201);

        } catch (\Exception $e) {
        $bud->destroy();
            return response()->json(['Message' => 'Something went wrong'], 500);
        }
        

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Buddhist  $buddhist
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $bud = Buddhist::where('id', $id)->with(["type", "user.verify"])->first();
        if (empty($bud)) {
            return response()->json([
                "message" => "No Data Found",
            ], 404);
        }
        return new OneBuddhistResource($bud);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Buddhist  $buddhist
     * @return \Illuminate\Http\Response
     */
    public function edit(Buddhist $buddhist, $id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Buddhist  $buddhist
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        /* $request->validate([
    'name'=>'required|min:3|max:30|string',
    'detail'=>'required|string|max:255',
    'end_datetime'=>'required|date|date_format:Y-m-d H:i:s|after:now',
    'price'=>'required|integer',
    'highest_price'=>'required|integer',
    // 'type_id'=>'required|string',
    // 'user_id'=>'required|string',
    ]);
    $bud = Buddhist::findOrFail($id);
    $bud->name = $request->name;
    $bud->detail = $request->detail;
    $bud->price= $request->price;
    $bud->end_datetime= $request->end_datetime;
    $bud->highest_price = $request->price;
    $bud->save();*/
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Buddhist  $buddhist
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $request->validate([
            "id" => "required",
        ]);
        $bud = Buddhist::findOrFail($request->id);
        if (empty($bud)) {
            return response()->json(["message" => "No data found"], 404);
        }
        if ($bud->user_id == Auth::id) {
            $database = app('firebase.database');
            $reference = $database->getReference('buddhist/' . $bud->id . '/')->remove();
            $reference = $database->getReference('Comments/' . $bud->id . '/')->remove();
            $path = public_path() . '/buddhist_images/' . $bud->image_path;

            if (\File::isDirectory($path)) {
                \File::deleteDirectory($path);
            }
            $bud->delete();
            return \response()->json(['message' => 'delete data complete'], 200);
        } else {
            return response()->json([
                "message" => "You don't have permission to delete it",
            ], 403);
        }
    }

    public function bidding(Request $request)
    {
       
        $request->validate([
            'bidding_price' => 'required|numeric|gt:0',
            'fcm_token' => 'required|string',
            'buddhist_id' => 'required|string',
        ]);

   


        if (Auth::user()->hasRole("admin") || Auth::user()->hasRole("superadmin")) {
            return response()->json(["message" => "only user can't use this function"], 403);
        }
        $bud = Buddhist::findOrFail($request->buddhist_id);
        if ($bud->active == 0) {
            return response()->json(["message" => "this buddhist not available to bid"], 404);
        }
        if (Auth::id() == $bud->user_id) {
            return response()->json(["message" => "You can't bid your buddhist"], 403);
        }

        
        //get Highest Price

        if (Carbon::now()->lessThan(Carbon::parse($bud->end_time))) {
            $database = app('firebase.database');
            $reference = $database->getReference('buddhist/' . $bud->id . '/')
                ->orderByChild('price')
                ->limitToLast(1)
                ->getSnapshot();
            $highest_price = 0;
            $data = $reference->getValue();
            foreach ($data as $key => $eachData);
            {
                $highest_price = $eachData['price'];
            }

            $reference1 = $database->getReference('buddhist/' . $bud->id . '/')
                ->orderByChild('uid')
                ->equalTo(Auth::user()->firebase_uid)
                ->getSnapshot();
            $data = $reference1->getValue();


          


            if ((int) $request->bidding_price > (int) $highest_price) {
                if ((int) $request->bidding_price - (int) $highest_price < (int) $bud->priceSmallest) {
                    return response()->json([
                        "message" => "??????????????????????????????????????????????????????????????? " . $bud->priceSmallest . " ?????????",
                    ]);
                }
                $maximunPrice = ($bud->highest_price*10000)/100;
                if ((int) $request->bidding_price > (int) $maximunPrice) {
                    return response()->json([
                        "message" => "?????????????????????????????????????????? 10000% ??????????????????????????????????????????",
                    ]);
                }


                $reference = $database->getReference('buddhist/' . $bud->id . '/')
                    ->push([
                        'is_verify'=>Auth::user()->getVerifyStatus(),
                        'time'=>Carbon::now()->format("Y-m-d H:i:s"),
                        'uid' => Auth::user()->firebase_uid, //bidder id
                        'price' => $request->bidding_price, // new highest price
                        'name' => Auth::user()->name,
                        'surname' => Auth::user()->surname,
                        'picture' => Auth::user()->getProfilePath(),
                        'id' => Auth::id(),
                    ]);
                $bud->highest_price = $request->bidding_price;
                $bud->highBidUser = Auth::id();
                if (Carbon::now()->diffInSeconds(Carbon::parse($bud->end_time)) <= 180) {
                    $bud->end_time = Carbon::parse($bud->end_time)->addMinutes(1);
                }
                $bud->winner_fcm_token = $request->fcm_token;
                $bud->winner_user_id = Auth::user()->firebase_uid;

                $bud->save();

                $ownerBuddhist = Buddhist::find($request->buddhist_id);
                $ownerID = $ownerBuddhist->user->id;
                $ownerFcmToken = $ownerBuddhist->user->firebase_uid;
                $ownerTopic = $ownerBuddhist->user->topic;

                $messaging = app('firebase.messaging');
                if (empty($data) && Auth::id() != $ownerID) {
                    $messaging->subscribeToTopic($ownerBuddhist->topic, $request->fcm_token);
                    $checkNofi = NotificationFirebase::where([
                        ["user_id", "!=", Auth::id()],
                        ["buddhist_id", $request->buddhist_id],
                        ["notification_type","bidding_participant"],
                    ])->first();
                    if(!$checkNofi) {
                        NotificationFirebase::create([
                            'notification_time' => date('Y-m-d H:i:s'),
                            'read' => 0,
                            'data' => $request->bidding_price,
                            'notification_type' => "bidding_participant",
                            'user_id' => Auth::id(),
                            'buddhist_id' => $request->buddhist_id,
                            'comment_path' => 'empty',
                        ]);
                    }else{
                       $checkNofi->update([
                       
                        'notification_time' => date('Y-m-d H:i:s'),
                        'data' => $request->bidding_price,
                        'read' => 0,
                    ]);
                    }
                   
                }

                      // get all data from notification to found all user that bid this round
                      $data = NotificationFirebase::where([
                        ["user_id", "!=", Auth::id()],
                        ["buddhist_id", $request->buddhist_id],
                    ])->where("notification_type", "bidding_participant")
                        ->update([
                            'notification_type' => "bidding_participant",
                            'notification_time' => date('Y-m-d H:i:s'),
                            'data' => $request->bidding_price,
                            'read' => 0,
                        ]);
                /* $bidding_notification = Notification::fromArray([
                'title' => '????????? ' . $ownerBuddhist->name,
                'body' => '??????????????????????????????????????????????????????????????????????????? ' . $request->bidding_price . ' ?????????',
                'image' => \public_path("/notification_images/chat.png"),

                ]);
                $bidding_notification_data = [
                'sender' => Auth::id(),
                'buddhist_id' => $request->buddhist_id,
                'page' => 'homepage',

                ];
                $bidding_message = CloudMessage::withTarget('topic', $ownerBuddhist->topic)
                ->withNotification($bidding_notification)
                ->withData($bidding_notification_data);
                $messaging->send($bidding_message);*/
                /*  $androidConfig = AndroidConfig::fromArray([
                'ttl' => '3600s',
                'priority' => 'high',

                ]);*/

                //$bidding_condition = "('" . $ownerBuddhist->topic . "') in topics && !('" . Auth::user()->topic . "' in topics)";
                try{
                    $bidding_message = CloudMessage::withTarget('condition', "'" . $ownerBuddhist->topic . "' in topics && !('" . Auth::user()->topic . "' in topics)")
                    ->withNotification(Notification::fromArray([
                        'title' => '????????? ' . $ownerBuddhist->name,
                        'body' => '??????????????????????????????????????????????????????????????????????????? ' . number_format($request->bidding_price,2,".",",") . ' ?????????',
                        'image' => \public_path("/notification_images/chat.png"),
                    ]))
                    ->withData([
                        'sender' => Auth::id(),
                        'buddhist_id' => $request->buddhist_id,
                        'type' => 'bidding',

                    ]);
                //  $bidding_message = $bidding_message->withAndroidConfig($androidConfig);
                $messaging->send($bidding_message);
                }catch(Exception $e){
                    $bidding_message = CloudMessage::withTarget('condition', "'" . $ownerBuddhist->topic . "' in topics && !('" . Auth::user()->topic . "' in topics)")
                    ->withNotification(Notification::fromArray([
                        'title' => '????????? ' . $ownerBuddhist->name,
                        'body' => '??????????????????????????????????????????????????????????????????????????? ' . number_format($request->bidding_price,2,".",",") . ' ?????????',
                        'image' => \public_path("/notification_images/chat.png"),
                    ]))
                    ->withData([
                        'sender' => Auth::id(),
                        'buddhist_id' => $request->buddhist_id,
                        'type' => 'bidding',

                    ]);
                //  $bidding_message = $bidding_message->withAndroidConfig($androidConfig);
                $messaging->send($bidding_message);
                }
            
                /* $owner_notification = Notification::fromArray([
                'title' => '????????? ' . $ownerBuddhist->name . ' ??????????????????????????????????????????',
                'body' => '?????????????????????????????????????????? ' . $request->bidding_price . ' ?????????',
                'image' => \public_path("/notification_images/chat.png"),
                ]);
                $owner_notification_data = [
                'sender' => Auth::id(),
                'buddhist_id' => $request->buddhist_id,
                'page' => 'content_detail',

                ];
                $owner_message = CloudMessage::withTarget('topic', $ownerTopic)
                ->withNotification($owner_notification)
                ->withData($owner_notification_data);
                $messaging->send($owner_message);*/

                try{
                    $owner_message = CloudMessage::withTarget('topic', $ownerTopic)
                    ->withNotification(Notification::fromArray([
                        'title' => '????????? ' . $ownerBuddhist->name . ' ??????????????????????????????????????????',
                        'body' => '?????????????????????????????????????????? ' .  number_format($request->bidding_price,2,".",",") . ' ?????????',
                        'image' => \public_path("/notification_images/chat.png"),
                    ]))
                    ->withData([
                        'sender' => Auth::id(),
                        'buddhist_id' => $request->buddhist_id,
                        'type' => '',

                    ]);
                // $owner_message = $owner_message->withAndroidConfig($androidConfig);
                $messaging->send($owner_message);
                }catch(Exception $e){
                    $owner_message = CloudMessage::withTarget('topic', $ownerTopic)
                    ->withNotification(Notification::fromArray([
                        'title' => '????????? ' . $ownerBuddhist->name . ' ??????????????????????????????????????????',
                        'body' => '?????????????????????????????????????????? ' .  number_format($request->bidding_price,2,".",",") . ' ?????????',
                        'image' => \public_path("/notification_images/chat.png"),
                    ]))
                    ->withData([
                        'sender' => Auth::id(),
                        'buddhist_id' => $request->buddhist_id,
                        'type' => '',

                    ]);
                // $owner_message = $owner_message->withAndroidConfig($androidConfig);
                $messaging->send($owner_message);
                }
               

          

                /*  if (empty($data)) {
                NotificationFirebase::create([
                'notification_time' => date('Y-m-d H:i:s'),
                'read' => 1,
                'data' => $request->bidding_price,
                'notification_type' => "bidding_participant",
                'user_id' => Auth::id(),
                'buddhist_id' => $request->buddhist_id,
                'comment_path' => 'empty',
                ]);

                } else {
                $data->update([
                'notification_time' => date('Y-m-d H:i:s'),
                'data' => $request->bidding_price,
                'read' => 1,
                ]);
                }*/

                //if (Auth::id() != $bud->user_id) {

                /* NotificationFirebase:where([
                ["buddhist_id", $request->buddhist_id],
                ["notification_type", "bidding_participant"],
                ["user_id", "!=", Auth::id()],
                ])->update([
                'read' => 1,
                ]);*/

                /*  NotificationFirebase::
                where([
                ["buddhist_id", $request->buddhist_id],
                ["notification_type", "bidding_participant"],
                ["user_id", "!=", Auth::id()],

                ]);*/

                /* $notificationData = NotificationFirebase::
                where([
                ["buddhist_id", $request->buddhist_id],
                ["notification_type", "bidding_participant"],
                ["user_id", "!=", Auth::id()],

                ])->select("user_id")->distinct()->get();

                for ($i = 0; $i < count($notificationData); $i++) {
                NotificationFirebase::create([
                'notification_time' => date('Y-m-d H:i:s'),
                'read' => 0,
                'data' => $request->bidding_price,
                'buddhist_id' => $request->buddhist_id,
                'user_id' => $notificationData[$i]["user_id"],
                'notification_type' => "bidding",
                'comment_path' => 'empty',
                ]);

                }*/

                //   }

                return response()->json(["message" => "Successfully"], 200);
            } else {
                return response()->json(["message" => "Your bidding price must more than " . $highest_price . " ?????????"], 400);
            }
        } else {
            return response()->json(['message' => 'this item is expired'], 404);
        }
    }

    public function buddhistType(Request $request,$type_id)
    {
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

        $buddhists =  DB::table("buddhists")->leftJoin('verifies',"buddhists.user_id",'=','verifies.user_id')
        ->where([['buddhists.type_id', $type_id],['buddhists.end_time', '>', Carbon::now()], ["buddhists.active", "1"]])
        ->select(
            'buddhists.id','buddhists.name','buddhists.price','buddhists.place','buddhists.end_time','buddhists.image_path','verifies.file_verify_status',
        )->paginate($perPage);

      //  $buddhists = Buddhist::where([['type_id', $type_id], ['end_time', '>', Carbon::now()], ["active", "1"]])->paginate($perPage);


        return buddhistCollection::collection($buddhists);
    }

    public function recommendedBuddhist(Request $request, $type_id, $buddhist_id)
    {
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
     
        $buddhist =  DB::table("buddhists")->leftJoin('verifies',"buddhists.user_id",'=','verifies.user_id')
        ->where([
            ['buddhists.end_time', '>', Carbon::now()], 
            ['buddhists.type_id', '=', $type_id],
            ["buddhists.active", "1"],
            ['buddhists.id', '!=', $buddhist_id],
            ])
        ->select(
            'buddhists.id','buddhists.name','buddhists.price','buddhists.highest_price','buddhists.place','buddhists.end_time','buddhists.image_path','verifies.file_verify_status'
        )
        ->orderBy("buddhists.created_at","desc")->paginate($perPage)->shuffle();
        // $buddhist = Buddhist::where([
        //     ['end_time', '>', Carbon::now()],
        //     ['type_id', '=', $type_id],
        //     ['id', '!=', $buddhist_id],
        //     ["active", "1"],
        // ])->with('type')->orderBy("created_at", "desc")->paginate($perPage)->shuffle();
        return BuddhistResource::collection($buddhist);

    }

    public function almostEnd(Request $request)
    {
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
        $bud =  DB::table("buddhists")->leftJoin('verifies',"buddhists.user_id",'=','verifies.user_id')
        ->where([
            ['buddhists.end_time', '>', Carbon::now()], 
            ["buddhists.active", "1"]])
        ->select(
            'buddhists.id','buddhists.name','buddhists.price','buddhists.highest_price','buddhists.place','buddhists.end_time','buddhists.image_path','verifies.file_verify_status'
        )
        ->orderBy("buddhists.end_time")->paginate($perPage);

      //  $bud = Buddhist::where([['end_time', '>', Carbon::now()], ["active", "1"]])->with('type')->orderBy("end_time")->paginate($perPage);
        return BuddhistResource::collection($bud);
    }
    public function myActiveBuddhist(Request $request)
    {
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

        $buddhist =  DB::table("buddhists")->leftJoin('verifies',"buddhists.user_id",'=','verifies.user_id')
        ->where([
            ["buddhists.active", "1"], 
            ["buddhists.end_time",'>', Carbon::now()],
            ["buddhists.user_id",Auth::id()]
           ])
        ->select(
            'buddhists.id','buddhists.name','buddhists.price','buddhists.highest_price','buddhists.place','buddhists.end_time','buddhists.image_path','verifies.file_verify_status'
        )->orderBy("buddhists.end_time")->paginate($perPage);

        // $buddhist = Buddhist::where([
        //     ["active", "1"],
        //     ["user_id", Auth::id()],
        //     ["end_time", '>', Carbon::now()],

        // ])->orderBy("end_time")->paginate($perPage);
        if (empty($buddhist)) {
            return response()->json(["message" => "No data found"], 200);
        }
        return BuddhistResource::collection($buddhist);

    }
    public function mySoldOutBuddhist(Request $request)
    {
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

        $buddhist =  DB::table("buddhists")->leftJoin('verifies',"buddhists.user_id",'=','verifies.user_id')
        ->where([
            ["buddhists.winner_user_id", "!=","empty"], 
            ["buddhists.end_time",'<', Carbon::now()],
            ["buddhists.user_id",Auth::id()]
           ])
        ->select(
            'buddhists.id','buddhists.name','buddhists.price','buddhists.highest_price','buddhists.place','buddhists.end_time','buddhists.image_path','verifies.file_verify_status'
        )->orderBy("buddhists.end_time")->paginate($perPage);

        // $buddhist = Buddhist::where([
        //     ["winner_user_id", "!=", "empty"],
        //     ["end_time", '<', Carbon::now()],
        //     ["user_id", Auth::id()],
        // ])->orderBy("end_time")->paginate($perPage);
        if (empty($buddhist)) {
            return response()->json(["message" => "No data found"], 200);
        }
        return BuddhistResource::collection($buddhist);

    }
    public function myNonSoldOutBuddhist(Request $request)
    {
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

        $buddhist =  DB::table("buddhists")->leftJoin('verifies',"buddhists.user_id",'=','verifies.user_id')
        ->where([
            ["buddhists.winner_user_id", "empty"], 
            ["buddhists.end_time",'<', Carbon::now()],
            ["buddhists.user_id",Auth::id()]
           ])
        ->select(
            'buddhists.id','buddhists.name','buddhists.price','buddhists.highest_price','buddhists.place','buddhists.end_time','buddhists.image_path','verifies.file_verify_status'
        )->orderBy("buddhists.end_time")->paginate($perPage);

        // $buddhist = Buddhist::where([
        //     ["user_id", Auth::id()],
        //     ["end_time", '<', Carbon::now()],
        //     ["winner_user_id", "empty"],
        // ])->orderBy("end_time")->paginate($perPage);
        if (empty($buddhist)) {
            return response()->json(["message" => "No data found"], 200);
        }
        return BuddhistResource::collection($buddhist);

    }
    public function biddingLose(Request $request)
    {
        #check if it's not your buddhist
        #get UID last from firebase
        #check my uid with uid from firebase
        #find buddhist that end

        /**
         * ?????????????????????????????????????????????????????????????????????
         * ?????????????????????????????????????????????????????????????????????????????????????????????
         * ???????????????????????????????????????????????????????????????
         */
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



        $data = DB::table('notification')->leftJoin("buddhists", "buddhists.id", "=", "notification.buddhist_id")
        ->leftJoin("verifies","verifies.user_id","=","buddhists.user_id")
            ->where([
                ['buddhists.end_time', '<', Carbon::now()],
                ['notification.notification_type', 'bidding_result'],
                ["notification.data", "!=", Auth::id()],
                ["notification.user_id",Auth::id()]
            ])
            ->select("buddhists.id", "buddhists.name", "buddhists.highest_price", "buddhists.image_path", "buddhists.end_time", "buddhists.highBidUser", "buddhists.place","verifies.file_verify_status")
            ->distinct()
            ->paginate($perPage);
           
            if (empty($data)) {
                return response()->json(["message" => "No data found"], 200);
            }
        return participantBiddingResource::collection($data);

    }
    public function biddingWin(Request $request)
    {
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

        $buddhist =  DB::table("buddhists")->leftJoin('verifies',"buddhists.user_id",'=','verifies.user_id')
        ->where([
          
            ["buddhists.end_time",'<', Carbon::now()],
            ["buddhists.winner_user_id", Auth::user()->firebase_uid]
           ])
        ->select(
            'buddhists.id','buddhists.name','buddhists.price','buddhists.highest_price','buddhists.place','buddhists.end_time','buddhists.image_path','verifies.file_verify_status'
        )->paginate($perPage);

        // $buddhist = Buddhist::where([
        //     ["end_time", '<', Carbon::now()],
        //     ["winner_user_id", Auth::user()->firebase_uid],
        // ])->paginate($perPage);
        if (empty($buddhist)) {
            return response()->json(["message" => "No data found"], 200);
        }
        return BuddhistResource::collection($buddhist);

    }

    public function checkBuddhistResult($id)
    {

        $buddhist = Buddhist::where("id", $id)->with("user")->first();
        if(!$buddhist){
            return response()->json(["data"=>[],"message"=>"no data found","success"=>false]);
        }
        // return response()->json(["data" => $buddhist], 200);
        return new checkBuddhistResultResource($buddhist);
    }

    public function participantBidding(Request $request)
    {
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

        $data = DB::table('notification')->leftJoin("buddhists", "buddhists.id", "=", "notification.buddhist_id")
        ->leftJoin("verifies","buddhists.user_id","=","verifies.user_id")
            ->where([
                ['buddhists.end_time', '>', Carbon::now()],
                ["notification.user_id", Auth::id()],
            ])
            ->whereIn("notification_type", ["bidding_participant"])
            ->select("buddhists.id", "buddhists.name", "buddhists.highest_price", "buddhists.image_path", "buddhists.end_time", "buddhists.highBidUser", "buddhists.place","verifies.file_verify_status")
            ->distinct()
            ->paginate($perPage);

        return participantBiddingResource::collection($data);
    }

    public function countByFavorite(Request $request)
    {
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
        // $data = Buddhist::select(['buddhists.id', 'buddhists.name', 'buddhists.price', 'buddhists.highest_price', 'buddhists.place', 'buddhists.end_time', 'buddhists.image_path', 'buddhists.type_id','verifies.file_verify_status', DB::raw('count(favourites.id) as total')])
        //     ->leftJoin('favourites', 'buddhists.id', '=', 'favourites.buddhist_id')
        //     ->leftJoin("verifies","buddhists.user_id","=","verifies.user_id")
        //     ->where('buddhists.end_time', '>', Carbon::now())
        //     ->groupBy('buddhists.id')
        //     ->orderBy('total', 'DESC')
        //     ->paginate($perPage);
        $data = Buddhist::select(['buddhists.id', 'buddhists.name', 'buddhists.price', 'buddhists.highest_price', 'buddhists.place', 'buddhists.end_time', 'buddhists.image_path', 'buddhists.type_id','verifies.file_verify_status', DB::raw('count(notification.buddhist_id) as total')])
          //  ->leftJoin('favourites', 'buddhists.id', '=', 'favourites.buddhist_id')
            ->leftJoin('notification','notification.buddhist_id','=','buddhists.id')
            ->leftJoin("verifies","buddhists.user_id","=","verifies.user_id")
            ->where([
                ['buddhists.end_time', '>', Carbon::now()],
                ['notification.notification_type','=','bidding_participant']
            ])
            ->groupBy('buddhists.id')
            ->orderBy('total', 'DESC')
            ->paginate($perPage);
           

        return BuddhistResource::collection($data);

    }

}
