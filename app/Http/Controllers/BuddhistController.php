<?php

namespace App\Http\Controllers;

use App\Http\Resources\buddhistCollection;
use App\Http\Resources\BuddhistResource;
use App\Http\Resources\OneBuddhistResource;
use App\Models\Buddhist;
use Auth;
use Carbon\Carbon;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Image;
use Kreait\Firebase\Messaging\Notification;
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
        $this->middleware('auth:api')->except('index', 'show', 'buddhistType','testTokenGetData');
    }
    public function index()
    {

        #nice
        $bud = Buddhist::where('end_time', '>', Carbon::now())->with('type')->get();
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
            'price' => 'required|integer',
            'images' => 'required|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,PNG|max:8192',
            'type_id' => 'required|string',

            'pay_choice' => 'required|string',
            'bank_name' => 'sometimes|string',
            'account_name' => 'sometimes|string',
            'account_number' => 'sometimes|string|max:12',

            'sending_choice' => 'required|string',
            'place_send' => 'sometimes|string',
            'tel' => 'sometimes|string',
            'more_info' => 'sometimes|string',

            'place' => 'required|string',
            'status' => 'required|string',
            
            'fcm_token'=>'required|string'
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
        $bud->pay_choice = $request->pay_choice;
        $bud->sending_choice = $request->sending_choice;
        $bud->place = $request->place;
        $bud->status = $request->status;
        if ($request->has('bank_name')) {
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
        }

        $folderName = uniqid() . "_" . time();
        if (!\File::isDirectory(public_path("/buddhist_images"))) {
            \File::makeDirectory(public_path('/buddhist_images'), 493, true);
        }
        if (!\File::isDirectory(public_path("/buddhist_images/" . $folderName))) {
            \File::makeDirectory(public_path('/buddhist_images/' . $folderName), 493, true);
        }

        

        $bud->image_path = $folderName;

        $bud->user_id = Auth::id();
        $bud->type_id = $request->type_id;
        $bud->highBidUser = Auth::id();
        $bud->save();
        $topic = "B".$bud->id;
        $messaging->subscribeToTopic($topic, $request->fcm_token);

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
                    'buddhist_id' => $request->buddhist_id,
                ]);
            return response()->json(['data' => $bud], 201);

        } catch (Exception $e) {
            $bud->destroy();
            return response()->json(['Message' => 'Something went wrong'],500);
        }

        //get Highest Data
        /*  $database = app('firebase.database');
    $reference = $database->getReference('buddhist/2/')
    ->orderByChild('price')
    ->limitToLast(1)
    ->getSnapshot()
    ->getValue();
    dd($reference);*/
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
    public function destroy($id)
    {

        $bud = Buddhist::findOrFail($id);
        $database = app('firebase.database');
        $reference = $database->getReference('buddhist/' . $bud->id . '/')->remove();
        $path = public_path() . '/buddhist_images/' . $bud->image_path;

        if (\File::isDirectory($path)) {
            \File::deleteDirectory($path);

        }
        $bud->delete();
        return \response()->json(['message' => 'delete data complete'], 200);
    }

    /* public function getHigh()
    {*/
/*$database = app('firebase.database');
$reference = $database->getReference('buddhist/2/')
->orderByChild('price')
->limitToLast(1)
->getSnapshot()
->getValue();
dd($reference);*/
    /* $database = app('firebase.database');
    $reference = $database->getReference('buddhist/2/')
    ->orderByChild('price')
    ->limitToLast(1)
    ->getSnapshot();
    $highest_price=0;
    $data = $reference->getValue();
    foreach($data as $key=>$eachData);
    {
    $highest_price= $eachData['price'];
    }
    dd($highest_price);
    }*/

    public function bidding(Request $request, $id)
    {

        $request->validate([
            'bidding_price' => 'required|integer',
        ]);
        $bud = Buddhist::find($id);
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
            if ($request->bidding_price > $highest_price) {

                $reference = $database->getReference('buddhist/' . $bud->id . '/')
                    ->push([
                        'uid' => Auth::user()->firebase_uid, //bidder id
                        'price' => $request->bidding_price, // new highest price
                    ]);
                $bud->highest_price = $request->bidding_price;
                $bud->highBidUser = Auth::id();
                if (Carbon::now()->diffInSeconds(Carbon::parse($bud->end_time)) <= 300) {
                    $bud->end_time = Carbon::parse($bud->end_time)->addMinutes(3);
                }

                $bud->save();
                return response()->json(["data" => "Successfully"], 200);
            } else {
                return response()->json(["data" => "Your bidding price must more than highest price"], 400);
            }
        } else {
            return response()->json(['data' => 'this item is expired'], 404);
        }
    }

    public function buddhistType($type_id)
    {

        $buddhists = Buddhist::where([['type_id', $type_id], ['end_time', '>', Carbon::now()]])->get();

        return buddhistCollection::collection($buddhists);
    }


    public function testTokenGetData()
    {
        

        $topicOwner = 'B1';
        $commentTopic = 'B1_C1';
        $database = app('firebase.database');
        $reference = $database->getRefercnce('');


        $topic = 'news';
        $notification = Notification::fromArray([
            'title'=>'ມີຄົນຄອມເມັ້ນພະທີ່ເຈົ້າລົງປະມູນ',
            'body'=>'ເຈີໄດ້ທາງດ່ວນ',
            'image'=>\public_path("/notification_images/chat.png")
        ]);
       

        $messaging = app('firebase.messaging');
        $message = CloudMessage::withTarget('topic',$topic)
        ->withNotification($notification)
        ->withData(['id'=>'1']);
        $messaging->send($message);
        return response()->json(['message'=>"send suc"],200);
    }
}
