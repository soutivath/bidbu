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
            $data = ChatRoom::create(
                [
                    "buddhist_id" => $request->buddhist_id,
                    "user_1" => Auth::user()->id,
                    "user_2" => $request->send_to,
                ]
            );
            $database = app("firebase.database");
            $database->getReference('chat_room/')
                ->set([
                    $request->buddhist_id => "",
                ]);
        }
        return response()->json(["data" => $request->buddhist_id], 200);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            "chat_room_id" => "required",
            "message" => "required",
            "send_to" => "required",
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

        $database = app('firebase.database');
        $reference = $database->getReference("chat_room/" . $request->chat_room_id . "/")
        ->orderByChild("send_by")
        ->equalTo(Auth::user()->id)
        ->limitToFirst(1)
        ->getSnapshot();
    $data = $reference->getValue();
    if (empty($data)) {

        NotificationFirebase::create([
            'notification_time' => date('Y-m-d H:i:s'),
            'read' => 0,
            'data' => $send_to_user,
            'buddhist_id' => $request->chat_room_id,
            'user_id' => Auth::id(),
            'notification_type' => "result_message",
            'comment_path' => 'chat_room/' . $request->chat_room_id . '/',

        ]);

    }



        $database = app("firebase.database");
        $database->getReference("chat_room/" . $request->chat_room_id . "/")
            ->push([
                "send_by" => Auth::user()->id,
                "time" => date('Y-m-d H:i:s'),
                "message" => $request->message,
                "read" => 0,
            ]);




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
