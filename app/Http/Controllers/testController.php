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
}
