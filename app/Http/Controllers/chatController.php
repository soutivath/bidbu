<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use Auth;
use DB;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class chatController extends Controller
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
        $current_chat_id = "";
        $send_to_user = $request->send_to;
        $current_user = Auth::id();
        $checkExistData = DB::table('chat_room')
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
                    "user_1" => Auth::id(),
                    "user_2" => $request->send_to,
                ]
            );
            $current_chat_id = $data->id;
            $database->getReference('chat_room/' . $current_chat_id . '/')
                ->set([]);
        } else {
            $current_chat_id = $checkExistData->id;
        }
        return response()->json(["data" => $current_chat_id], 200);
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
        $database = app("firebase.database");
        $database->getReference("Chat_Messages/" . $request->chat_room . "/")
            ->push([
                "send_by" => Auth::id(),
                "time" => date('Y-m-d H:i:s'),
                "message" => $request->message,
                "read" => 0,
            ]);

        $user = ChatRoom::where([["id", $chat_room_id], ["user_1", Auth::id()]])->first();
        $topic_name = "";
        if ($user->user_1 == Auth::id()) {
            $topic_name = $user_id->user2->topic;
            $chat_message = CloudMessage::withTarget('topic', $topic_name)
                ->withNotification(Notification::fromArray([
                    'title' => 'ຂໍ້ຄວາມໃຫມ່ຈາກ ' . Auth::user()->name,
                    'body' => $request->message,
                ]))
                ->withData([
                    'sender' => Auth::id(),
                    'chat_room_id' => $chat_room_id,
                    'type' => 'message',
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
                    'sender' => Auth::id(),
                    'chat_room_id' => $chat_room_id,
                    'type' => 'message',
                ]);
            $chat_message = $chat_message->withAndroidConfig($androidConfig);
            $messaging->send($chat_message);

        }
        $database = app("firebase.database");
        $database->getReference("Chat_Messages/" . $request->chat_room . "/")
            ->push([
                "send_by" => Auth::id(),
                "time" => date('Y-m-d H:i:s'),
                "message" => $request->message,
                "read" => 0,
            ]);

        return response()->json(["message" => "message send"], 201);

    }
}
