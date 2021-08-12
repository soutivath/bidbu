<?php

namespace App\Http\Controllers;

use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;

class testController extends Controller
{
    /* public function checkBuddhistResult($buddhist_id)
    {
    $buddhist = Buddhist::findOrFail($buddhist_id)->with("user")->first();

    return new checkBuddhistResultResource($buddhist);
    }

    public function participantBidding()
    {
    $data = DB::table('notification')->leftJoin("buddhists", "buddhists.id", "=", "notification.user_id")
    ->where([
    ['buddhists.end_time', '>', Carbon::now()],
    ['notification.notification_type', 'bidding_participant'],
    // ["notification.", Auth::id()],
    ])
    ->select("buddhists.id", "buddhists.name", "buddhists.highest_price", "buddhists.image_path", "buddhists.end_time", "buddhists.highBidUser")
    ->distinct()
    ->get();

    return participantBiddingResource::collection($data);
    }*/
    public function testNotification()
    {
        $messaging = app("firebase.messaging");
        $config = AndroidConfig::fromArray([
            'ttl' => '3600s',
            'priority' => 'high',
            'notification' => [
                'title' => '$GOOG up 1.43% on the day',
                'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
                'icon' => 'stock_ticker_update',
                'color' => '#f45342',
            ],
        ]);
        $message = CloudMessage::withTarget('topic', "notification_topic_pGlQ2NXZwUTOJh8CypSHsvlP4vn1")
            ->withNotification(['title' => 'Notification title', 'body' => 'Notification body', "mutable_content" => true,
                "content_available" => true]);

        $message = $message->withAndroidConfig($config);
        $messaging->send($message);

        return response()->json(["message" => "nice"], 200);
    }
}
