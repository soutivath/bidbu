<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use Auth;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Http\Request;

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
            "send_to" => "required",
            "buddhist_id" => "required",
        ]);
        // search data

        $checkExistData = DB::table('chat_room')
            ->where(function ($query) {
                $query->where("user_1", Auth::id());
                $query->where("user_2", $request->send_to);
            })
            ->orWhere(function ($query) {
                $query->where("user_1", $request->send_to);
                $query->where("user_2", Auth::id());
            })
            ->get();
        if ($checkExistData->isEmpty()) {
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
