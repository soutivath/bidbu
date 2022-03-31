<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use Auth;
use DB;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\NotificationFirebase;
use Intervention\Image\Facades\Image;
class InboxChatController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware("auth:api");
        $this->middleware('isUserActive:api');

    }

    public function createChatRoom(Request $request)
    {
        //when click on the chat check if user has been pair up or not
        $request->validate([
            "send_to" => "required|integer",
            "buddhist_id" => "required|integer",
        ]);
        // search data

        $send_to_user = $request->send_to;

        $current_user = Auth::user()->id;
        $checkExistData = DB::table('chat_room')
            ->where("buddhist_id", $request->buddhist_id)
            ->where(function ($query) use ($send_to_user, $current_user) {
                $query->where("user_1", $current_user)
                    ->where("user_2", $send_to_user);
            })
            ->orWhere(function ($query2) use ($send_to_user, $current_user) {
                $query2->where("user_1", $send_to_user)
                    ->where("user_2", $current_user);
            })
            ->first();
        if (empty($checkExistData)) {
            ChatRoom::create(
                [
                    "buddhist_id" => $request->buddhist_id,
                    "user_1" => Auth::user()->id,
                    "user_2" => $request->send_to,
                ]
            );

        }
        return response()->json(["data" => $request->buddhist_id], 200);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            "chat_room_id" => "required",
            "message" => "required",
            "send_to" => "required",
            'images' => 'sometimes|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,PNG|max:30720',
        ]);
        $messaging = app('firebase.messaging');

        $androidConfig = AndroidConfig::fromArray([
            'ttl' => '3600s',
            'priority' => 'high',
        ]);


        $current_user = Auth::user()->id;
        $send_to_user = $request->send_to;
        $user = ChatRoom::where("buddhist_id", $request->chat_room_id)
            ->where(function ($query) use ($send_to_user, $current_user) {
                $query->where("user_1", $current_user)
                    ->where("user_2", $send_to_user);
            })
            ->orWhere(function ($query2) use ($send_to_user, $current_user) {
                $query2->where("user_1", $send_to_user)
                    ->where("user_2", $current_user);
            })
            ->with(["user1", "user2"])
            ->first();

        $topic_name = "";


        if (empty($user)) {
            return response()->json(["message" => "no data found"], 404);
        }

        //update or create notification for admin chat user



        NotificationFirebase::firstOrCreate(
            [
                "buddhist_id"=>$request->chat_room_id,
                "user_id"=>$send_to_user,
                "notification_type"=>"result_message"
            ],
            [
                'notification_time' => date('Y-m-d H:i:s'),
                'read' => 0,
                'data' => $request->message,
                'comment_path' => 'chat_room/' . $request->chat_room_id . '/',
            ]
            );
            $database = app("firebase.database");
            if($request->has("images")){
               
                if (!\File::isDirectory(public_path("/chat_images"))) {
                    \File::makeDirectory(public_path('/chat_images'), 493, true);
                }
                if (!\File::isDirectory(public_path("/chat_images/" . $request->chat_room_id))) {
                    \File::makeDirectory(public_path('/chat_images/' . $request->chat_room_id), 493, true);
                    
                }
              foreach ($request->images as $image) {
                    $fileExtension = $image->getClientOriginalExtension();
                    $fileName = 'chat_images' . \uniqid() . "_" . time() . '.' . $fileExtension;
                    $location = public_path("/chat_images/" . $request->chat_room_id . "/" . $fileName);
                    Image::make($image)->resize(800, null, function ($constraint) {$constraint->aspectRatio();})->save($location);
                   
                     
                    $database->getReference("chat_room/" . $request->chat_room_id . "/")
                    ->push([
                        "send_by" => Auth::user()->id,
                        "time" => date('Y-m-d H:i:s'),
                        "message" => Config("values.APP_URL") . ":" . $_SERVER["SERVER_PORT"] .
                        "/" . "chat_images/" .$request->chat_room_id."/". $fileName,
                        "read" => 0,
                        "is_image"=>true
                    ]);
                }
        
            }
            else{
               
                $database->getReference("chat_room/" . $request->chat_room_id . "/")
                    ->push([
                        "send_by" => Auth::user()->id,
                        "time" => date('Y-m-d H:i:s'),
                        "message" => $request->message,
                        "read" => 0,
                        "is_image"=>false
                    ]);
            }



     




        if ($user->user1->id == Auth::user()->id) {
            $topic_name = $user->user2->topic;
            $chat_message = CloudMessage::withTarget('topic', $topic_name)
                ->withNotification(Notification::fromArray([
                    'title' => 'ຂໍ້ຄວາມໃຫມ່ຈາກ ' . Auth::user()->name,
                    'body' => $request->message,
                ]))
                ->withData([
                    'sender' => Auth::user()->id,
                    'chat_room_id' => $request->chat_room_id,
                    'type' => 'chat',
                ]);
            $chat_message = $chat_message->withAndroidConfig($androidConfig);
            $messaging->send($chat_message);

        } else {
            $topic_name = $user->user1->topic;
            $chat_message = CloudMessage::withTarget('topic', $topic_name)
                ->withNotification(Notification::fromArray([
                    'title' => 'ຂໍ້ຄວາມໃຫມ່ຈາກ ' . Auth::user()->name,
                    'body' => $request->message,
                ]))
                ->withData([
                    'sender' => Auth::user()->id,
                    'chat_room_id' => $request->chat_room_id,
                    'type' => 'chat',
                ]);
            $chat_message = $chat_message->withAndroidConfig($androidConfig);
            $messaging->send($chat_message);

        }

        return response()->json(["message" => "message send"], 201);

    }
}
