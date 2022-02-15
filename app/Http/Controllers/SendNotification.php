<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
class SendNotification extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:api')->except(["index", "show"]);
        $this->middleware('isUserActive:api')->except(["index", "show"]);
    }
    public function sendAll(Request $request)
    {
        $request->validate([
            "message"=>"required|string"
        ]);
        $users = User::select(["topic"])->where("id","!=","1")->get()->makeVisible("topic");
        $messaging = app('firebase.messaging');
        $androidConfig = AndroidConfig::fromArray([
            'ttl' => '3600s',
            'priority' => 'high',
        ]);
        foreach($users as $user)
        {
            $message = CloudMessage::withTarget('topic', $user["topic"])
        ->withNotification(Notification::fromArray([
            'title' => 'ຈາກ Kongdee',
            'body' => $request->message,
        ]))
        ->withData([]);
        $message = $message->withAndroidConfig($androidConfig);
        $messaging->send($message);
        }
        
       


        
    return response()->json([
        "message"=>"send message successfully"
    ]);

    }
}
