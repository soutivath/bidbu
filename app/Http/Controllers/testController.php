<?php

namespace App\Http\Controllers;

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
        /* $buddhist = Buddhist::findOrFail($id)->with("user")->first();

        return new checkBuddhistResultResource($buddhist);*/

        $messaging = app('firebase.messaging');
        $appInstance = $messaging->getAppInstance("e1pihRThRzGrGujAoLnZRT:APA91bHsRzBo6qP-nMCD-rP1V5I3G_W63sCp-fLawithIX6qycSL773085X7SZUrxfuG9NQfLO8l5J7UYL8Uvb7klVS8w3qxsVdb2v_q7QSTAbAulLRjpKouaCfowa96PuTfPH7BpEYb");
        $subscriptions = $appInstance->topicSubscriptions();

        foreach ($subscriptions as $subscription) {
            echo "{$subscription->registrationToken()} is subscribed to {$subscription->topic()}\n";
        }
$appInstance = $messaging->getAppInstance('<registration token>');

foreach ($appInstance->topicSubscriptions() as $subscription) {
    $messaging->unsubscribeFromTopic($subscription->topic(), $subscription->registrationToken());
}

        return $appInstance->rawData();

    }
}
