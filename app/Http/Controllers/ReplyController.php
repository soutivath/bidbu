<?php

namespace App\Http\Controllers;

use App\Models\Reply;
use Illuminate\Http\Request;
use Auth;
use Carbon\carbon;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Models\Buddhist;
class ReplyController extends Controller
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
        //
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
    public function store(Request $request,$buddhist_id,$comment_id)
    {
        
        $request->validate([
            'message'=>'required|string|max:255',
            'fcm_token'=>'required|string'
        ]);

        $messaging = app('firebase.messaging');
        $database = app('firebase.database');
        $result = $messaging->validateRegistrationTokens($request->fcm_token);
        if ($result['invalid'] != null) {
            return response()->json(['data' => 'your json token is invalid'], 404);
        }
        $reference = $database->getReference('Comments/' . $buddhist_id . '/'.$comment_id.'/replies/');
        $data = $reference
                ->orderByChild("uid")
                ->equalTo(Auth::user()->firebase_uid)
                ->limitToFirst(1)
                ->getSnapShot()
                ->getValue();
        $data = $reference->getValue();
        
        $owner = Buddhist::find($buddhist_id);
        $ownerID = $owner->user_id;
        if (empty($data)&&Auth::id()!=$ownerID) {
            $topic = "B".$buddhist_id."_C".$comment_id;
            $result = $messaging->subscribeToTopic($topic, $request->fcm_token);
        }
        try{
            $database = app('firebase.database');
            $reference = $database->getReference('Comments/'.$buddhist_id.'/'.$comment_id.'/replies/')
            ->push([
                'uid'=>Auth::user()->firebase_uid,
                'message'=>$request->message,
                'name'=>Auth::user()->name,
                'datetime'=>Carbon::now(),
            ]);


            $notification = Notification::fromArray([
                'title' => 'ທ່ານມີແຈ້ງເຕຶອນໃໝ່ຈາກ'.Buddhist::find($buddhist_id)->name.'ທີ່ທ່ານໄດ້ລົງປະມູນ',
                'body' => $request->message,
                'image' => \public_path("/notification_images/chat.png"),
            ]);
            $notification_data = [
                'buddhist_id' => $buddhist_id,
                'comment_id' => $comment_id,
                'sender'=>Auth::id(),
                "page"=> 'listreplies',
            ];
            $message = CloudMessage::withTarget('topic', "B".$buddhist_id)
                ->withNotification($notification)
                ->withData($notification_data);
            $messaging->send($message);

            $notification = Notification::fromArray([
                'title' => 'ມີຄົນຕອບກັບຄວາມຄິດເຫັນຂອງເຈົ້າຈາກ'.Buddhist::find($buddhist_id)->name,
                'body' => $request->message,
                'image' => \public_path("/notification_images/chat.png"),
            ]);
            $message = CloudMessage::withTarget('topic', "B".$buddhist_id."C".$comment_id)
                ->withNotification($notification)
                ->withData($notification_data);
            $messaging->send($message);
      
            return response()->json(['data'=>$reply],200);
        }catch(Exception $e)
        {
            $reply->destroy();
            return response()->json(['message'=>'Something went wrong'],500);
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function show(Reply $reply)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function edit(Reply $reply)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$buddhist_id,$comment_id,$reply_id)
    {
        $request->validate([
            'message'=>'required|string|max:255',
            
        ]);
        try{
            $database = app('firebase.database');
            $reference = $database->getReference('Comments/' . $buddhist_id . '/' . $comment_id."/replies/".$reply_id);
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
     * @param  \App\Models\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function destroy($buddhist_id,$comment_id,$reply_id)
    {
        
        $database = app('firebase.database');
        $reference = $database->getReference('Comments/' . $buddhist_id . '/' . $comment_id."/replies/".$reply_id);
        $ownerID = $reference->getSnapShot()->getValue();
       if(empty($ownerID)){
        return response()->json([
            "message"=>"Data Not Found"
        ]);
       }
          
        
       
        if($ownerID["uid"]==Auth::user()->firebase_uid)
        {
           
            try{
                $reference = $database->getReference('Comments/'.$buddhist_id.'/'.$comment_id.'/replies/'.$reply_id)->remove();
                return Response()->json(['message'=>'Delete Complete'],200);
            }catch(Exception $e)
            {
                return response()->json(
                    [
                        'message'=>'Something went wrong'.$e
                    ]
                    );
            }
        }
        else{
            return Response()->json(['error'=>'You Can\'t Delete this reply']);
        }
    }
}
