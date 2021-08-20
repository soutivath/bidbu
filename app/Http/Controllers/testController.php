<?php

namespace App\Http\Controllers;

use App\Models\Buddhist;
use carbon\Carbon;
use DB;

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
        /*$database = app("firebase.database");
        $reference1 = $database->getReference('buddhist/73')
        ->orderByChild('uid')
        ->equalTo("AehWUUU8IwPYqkmYUESdUzHBQro1")
        ->getSnapshot();
        $data = $reference1->getValue();

        if (empty($data)) {
        return response(["message" => "no data"], 200);
        } else {
        return response(["message" => " has data"], 200);

        }*/
        /*  NotificationFirebase::create([
        'notification_time' => date('Y-m-d H:i:s'),
        'read' => 1,
        'data' => 0,
        'notification_type' => "empty_bidding",
        'user_id' => 15,
        'buddhist_id' => 72,
        'comment_path' => 'empty',
        ]);
        return response()->json(["message" => "save data complete"], 200);*/
        /*  $data = Buddhist::select(['buddhists.name', DB::raw('count(favourites.id) as total')])
        ->leftJoin('favourites', 'buddhists.id', '=', 'favourites.buddhist_id')
        ->where('buddhists.end_time', '>', Carbon::now())
        ->groupBy('buddhists.id')
        ->orderBy('total', 'DESC')
        ->get();
        return response()->json(["data" => $data], 200);*/
        $data = Buddhist::select(['buddhists.id,buddhists.name,buddhists.price,buddhists.highest_price,buddhists.place,buddhists.end_time,buddhists.image_path,buddhists.type_id', DB::raw('count(favourites.id) as total')])
            ->leftJoin('favourites', 'buddhists.id', '=', 'favourites.buddhist_id')
            ->with("type")
            ->where('buddhists.end_time', '>', Carbon::now())
            ->groupBy('buddhists.id')
            ->orderBy('total', 'DESC')
            ->get();

        return response()->json(["data" => $data], 200);

    }
}
