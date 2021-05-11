<?php

namespace App\Http\Controllers;

use App\Models\Buddhist;
use App\Models\comment;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function index()
    {
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
    public function store(Request $request, $buddhist_id)
    {  
        

        $request->validate([
            'message' => 'required|string|max:255',
            'fcm_token' => 'required|string',
        ]);
        try{
        $database = app('firebase.database');
        $messaging = app('firebase.messaging');
        $result = $messaging->validateRegistrationTokens($request->fcm_token);
        if ($result['invalid'] != null) {
            return response()->json(['data' => 'your json token is invalid'], 404);

        }
        // search data
        $reference = $database->getReference('Comments/' . $buddhist_id . '/')
        ->orderByChild("uid")
        ->equalTo(Auth::user()->firebase_uid)
        ->limitToFirst(1)
        ->getSnapshot();
        $data = $reference->getValue();
        $key = array_keys($data);
        $comment_id = $key[0];
        //sub to topic
        $owner = Buddhist::find($buddhist_id);
        $ownerID = $owner->user_id;
       
        if (empty($data)&&Auth::id()!=$ownerID) {
            
            $topic = "B".$buddhist_id."_C".$comment_id;
            $result = $messaging->subscribeToTopic($topic, $request->fcm_token);
        }
        $reference = $database->getReference('Comments/' . $request->buddhist_id . '/')
                ->push([
                    'uid' => Auth::user()->firebase_uid,
                    'message' => $request->message,
                    'name' => Auth::user()->name,
                    'datetime' => Carbon::now(),
                    'replies' => '',
                ]);

                $notification = Notification::fromArray([
                    'title' => 'ທ່ານມີການສະແດງຄວາມຄິດເຫັນໃໝ່ຈາກ '.Buddhist::find($buddhist_id)->name.' ທີ່ທ່ານໄດ້ປ່ອຍ',
                    'body' => $request->message,
                    'image' => \public_path("/notification_images/chat.png"),
                ]);
                $notification_data = [
                    'sender'=>Auth::id(),
                    'buddhist_id' => $buddhist_id,
                    'comment_id' =>$comment_id,
                    'page'=>'content_detail'
                ];
                $message = CloudMessage::withTarget('topic', "B".$buddhist_id)
                    ->withNotification($notification)
                    ->withData($notification_data);
                $messaging->send($message);
    
                //*******/
                $notification = Notification::fromArray([
                    'title' => 'ທ່ານມີແຈ້ງເຕຶອນໃໝ່ຈາກ '.Buddhist::find($buddhist_id)->name.' ທີ່ທ່ານໄດ້ສະແດງຄວາມຄິດເຫັນ',
                    'body' => $request->message,
                    'image' => \public_path("/notification_images/chat.png"),
                ]);
                $notification_data = [
                    'sender'=>Auth::id(),
                    'buddhist_id' => $buddhist_id,
                    'comment_id' => $comment_id,
                    'page'=>'content_detail'
                ];
                $message = CloudMessage::withTarget('topic', "B".$buddhist_id."_C".$comment_id)
                    ->withNotification($notification)
                    ->withData($notification_data);
                $messaging->send($message);
        return response()->json([
            "data"=>$reference->getValue()
        ],201);
    } catch (Exception $e) {
        return response()->json(['Message' => 'Something went wrong'], 500);
    }
      
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function edit(comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $buddhist_id, $comment_id)
    {
      
        $request->validate([
            'message' => 'required|string|max:255',
        ]);
        try{
        $database = app('firebase.database');
        $reference = $database->getReference('Comments/' . $buddhist_id . '/' . $comment_id);
        $ownerID = $reference->getSnapShot()->getValue();
        if(empty($ownerID)){
            return response()->json([
                "message"=>"Data Not Found"
            ]);
           }
        if($ownerID["uid"]==Auth::user()->firebase_uid)
        {
            $reference->update([
                "message"=>$request->message
            ]);
            return response()->json([
                "message"=>"Update your message successfully"
            ],200);
        }else{
            return Response()->json(['error' => 'You can\'t edit this comment'], 400);
        }
       
    } catch (Exception $e) {
        return response()->json(
            [
                'message' => 'Something went wrong',
            ],
            500
        );
    }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy($buddhist_id, $comment_id)
    {
        $database = app('firebase.database');
        $reference = $database->getReference('Comments/' . $buddhist_id . '/' . $comment_id);
        $ownerID = $reference->getSnapShot()->getValue();
        if(empty($ownerID)){
            return response()->json([
                "message"=>"Data Not Found"
            ]);
           }
        if($ownerID["uid"]==Auth::user()->firebase_uid)
        {
            $database = app('firebase.database');
            try {
                $reference = $database->getReference('Comments/' . $buddhist_id . '/' . $comment_id)->remove();
                return Response()->json(['message' => 'Delete Complete'], 200);
            } catch (Exception $e) {
                return response()->json(
                    [
                        'message' => 'Something went wrong',
                    ],
                    500
                );
            }
        } else {
            return Response()->json(['error' => 'You Can\'t Delete this Comment'], 400);
        }

    }
}
