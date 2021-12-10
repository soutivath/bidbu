<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
class SendNotification extends Controller
{
    //
    public function sendAll(Request $request)
    {
        $request->validate([
            "message"=>"required|string"
        ]);
        $messaging = app('firebase.messaging');
        $androidConfig = AndroidConfig::fromArray([
            'ttl' => '3600s',
            'priority' => 'high',
        ]);


        $message = CloudMessage::withTarget('topic', "admin_topic_kongdee")
        ->withNotification(Notification::fromArray([
            'title' => 'ຈາກ Kongdee',
            'body' => $request->mesage,
        ]))
        ->withData([]);
    $message = $message->withAndroidConfig($androidConfig);
    $messaging->send($message);
    return response()->json([
        "message"=>"send message successfully"
    ]);

    }
}
