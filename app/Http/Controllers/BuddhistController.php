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
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;

class BuddhistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth:api')->except('index', 'show', 'buddhistType', 'recommendedBuddhist');
        $this->middleware('isUserActive:api')->except('index', 'show', 'buddhistType', 'recommendedBuddhist');
    }
    public function index()
    {

        $bud = Buddhist::where([['end_time', '>', Carbon::now()], ["active", "1"]])->with('type')->orderBy("created_at", "desc")->get();
        return BuddhistResource::collection($bud);
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
            'name' => 'required|min:3|max:30|string',
            'detail' => 'required|string|max:255',
            'end_datetime' => 'required|date|date_format:Y-m-d H:i:s|after:now',
            'price' => 'required|numeric|gt:0',
            'images' => 'required|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,PNG|max:8192',
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
        ]);
        $messaging = app('firebase.messaging');
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
            return response()->json(['data' => $bud], 201);

        } catch (Exception $e) {
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

        $bud = Buddhist::where('id', $id)->with(["type", "user"])->first();
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
        $bud = Buddhist::findOrFail($id);
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
                        "message" => "ຂັ້ນຕ່ຳໃນການປະມູນແມ່ນ " . $bud->priceSmallest . " ກີບ",
                    ]);
                }
                $reference = $database->getReference('buddhist/' . $bud->id . '/')
                    ->push([
                        'uid' => Auth::user()->firebase_uid, //bidder id
                        'price' => $request->bidding_price, // new highest price
                        'name' => Auth::user()->name,
                        'picture' => Auth::user()->getProfilePath(),
                    ]);
                $bud->highest_price = $request->bidding_price;
                $bud->highBidUser = Auth::id();
                if (Carbon::now()->diffInSeconds(Carbon::parse($bud->end_time)) <= 180) {
                    $bud->end_time = Carbon::parse($bud->end_time)->addMinutes(3);
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
                }

                /* $bidding_notification = Notification::fromArray([
                'title' => 'ຈາກ ' . $ownerBuddhist->name,
                'body' => 'ມີຄົນໃຫ້ລາຄາສູງກວ່າໃນລາຄາ ' . $request->bidding_price . ' ກີບ',
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
                $androidConfig = AndroidConfig::fromArray([
                    'ttl' => '3600s',
                    'priority' => 'high',

                ]);

                $bidding_message = CloudMessage::withTarget('topic', $ownerBuddhist->topic)
                    ->withNotification(Notification::fromArray([
                        'title' => 'ຈາກ ' . $ownerBuddhist->name,
                        'body' => 'ມີຄົນໃຫ້ລາຄາສູງກວ່າໃນລາຄາ ' . $request->bidding_price . ' ກີບ',
                        'image' => \public_path("/notification_images/chat.png"),
                    ]))
                    ->withData([
                        'sender' => Auth::id(),
                        'buddhist_id' => $request->buddhist_id,
                        'type' => 'bidding',

                    ]);
                $bidding_message = $bidding_message->withAndroidConfig($androidConfig);
                $messaging->send($bidding_message);

                /* $owner_notification = Notification::fromArray([
                'title' => 'ຈາກ ' . $ownerBuddhist->name . ' ທີ່ທ່ານໄດ້ປ່ອຍ',
                'body' => 'ມີຄົນສະເໜີລາຄາ ' . $request->bidding_price . ' ກີບ',
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
                $owner_message = CloudMessage::withTarget('topic', $ownerTopic)
                    ->withNotification(Notification::fromArray([
                        'title' => 'ຈາກ ' . $ownerBuddhist->name . ' ທີ່ທ່ານໄດ້ປ່ອຍ',
                        'body' => 'ມີຄົນສະເໜີລາຄາ ' . $request->bidding_price . ' ກີບ',
                        'image' => \public_path("/notification_images/chat.png"),
                    ]))
                    ->withData([
                        'sender' => Auth::id(),
                        'buddhist_id' => $request->buddhist_id,
                        'type' => '',

                    ]);
                $owner_message = $owner_message->withAndroidConfig($androidConfig);
                $messaging->send($owner_message);

                // get all data from notification to found all user that bid this round
                NotificationFirebase::create([
                    'notification_time' => date('Y-m-d H:i:s'),
                    'read' => 1,
                    'data' => $request->bidding_price,
                    'notification_type' => "bidding_participant",
                    'user_id' => Auth::id(),
                    'buddhist_id' => $request->buddhist_id,
                    'comment_path' => 'empty',
                ]);

                if (Auth::id() != $bud->user_id) {

                    $notificationData = NotificationFirebase::
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

                    }

                }

                return response()->json(["message" => "Successfully"], 200);
            } else {
                return response()->json(["message" => "Your bidding price must more than " . $highest_price . " ກີບ"], 400);
            }
        } else {
            return response()->json(['message' => 'this item is expired'], 404);
        }
    }

    public function buddhistType($type_id)
    {

        $buddhists = Buddhist::where([['type_id', $type_id], ['end_time', '>', Carbon::now()], ["active", "1"]])->get();

        return buddhistCollection::collection($buddhists);
    }

    public function recommendedBuddhist(Request $request, $type_id, $buddhist_id)
    {
        $buddhist = Buddhist::where([
            ['end_time', '>', Carbon::now()],
            ['type_id', '=', $type_id],
            ['id', '!=', $buddhist_id],
            ["active", "1"],
        ])->with('type')->orderBy("created_at", "desc")->get()->shuffle();
        return BuddhistResource::collection($buddhist);

    }

    public function almostEnd()
    {
        $bud = Buddhist::where([['end_time', '>', Carbon::now()], ["active", "1"]])->with('type')->orderBy("end_time")->get();
        return BuddhistResource::collection($bud);
    }
    public function myActiveBuddhist()
    {
        $buddhist = Buddhist::where([
            ["active", "1"],
            ["user_id", Auth::id()],
            ["end_time", '>', Carbon::now()],

        ])->orderBy("end_time")->get();
        if (empty($buddhist)) {
            return response()->json(["message" => "No data found"], 200);
        }
        return BuddhistResource::collection($buddhist);

    }
    public function mySoldOutBuddhist()
    {
        $buddhist = Buddhist::where([
            ["winner_user_id", "!=", "empty"],
            ["end_time", '<', Carbon::now()],
            ["user_id", Auth::id()],
        ])->orderBy("end_time")->get();
        if (empty($buddhist)) {
            return response()->json(["message" => "No data found"], 200);
        }
        return BuddhistResource::collection($buddhist);

    }
    public function myNonSoldOutBuddhist()
    {
        $buddhist = Buddhist::where([
            ["user_id", Auth::id()],
            ["end_time", '<', Carbon::now()],
            ["winner_user_id", "empty"],
        ])->orderBy("end_time")->get();
        if (empty($buddhist)) {
            return response()->json(["message" => "No data found"], 200);
        }
        return BuddhistResource::collection($buddhist);

    }
    public function biddingLose()
    {
        #check if it's not your buddhist
        #get UID last from firebase
        #check my uid with uid from firebase
        #find buddhist that end

        /**
         * ເຄື່ອງທີ່ປະມູນພະນັ້ນບໍ້
         * ້ເບິ່ງກ່່ອນວ່າມຶງປິມູນພະນັ່ນບໍ່
         * ກວດເວລາຫມົດກັບຜູ້ຊະນະ
         */

        $buddhist = Buddhist::where([
            ["user_id", Auth::id()],
            ["winner_user_id", "empty"],
        ])->get();
        if (empty($buddhist)) {
            return response()->json(["message" => "No data found"], 200);
        }
        return response()->json(["data" => $buddhist], 200);

    }
    public function biddingWin()
    {
        $buddhist = Buddhist::where([
            ["end_time", '<', Carbon::now()],
            ["winner_user_id", Auth::user()->firebase_uid],
        ])->get();
        if (empty($buddhist)) {
            return response()->json(["message" => "No data found"], 200);
        }
        return BuddhistResource::collection($buddhist);

    }

    public function checkBuddhistResult($id)
    {

        $buddhist = Buddhist::where("id", $id)->with("user")->get();
        if ($buddhist->isEmpty()) {
            return response()->json(["data" => "no data"], 204);
        }
        //return response()->json(["data" => $buddhist], 200);
        return new checkBuddhistResultResource($buddhist);
    }

    public function participantBidding()
    {
        $data = DB::table('notification')->leftJoin("buddhists", "buddhists.id", "=", "notification.buddhist_id")
            ->where([
                ['buddhists.end_time', '>', Carbon::now()],
                ['notification.notification_type', 'bidding_participant'],
                ["notification.user_id", Auth::id()],
            ])
            ->select("buddhists.id", "buddhists.name", "buddhists.highest_price", "buddhists.image_path", "buddhists.end_time", "buddhists.highBidUser")
            ->distinct()
            ->get();

        return participantBiddingResource::collection($data);
    }

}
