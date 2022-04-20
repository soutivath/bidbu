<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Constants\Phone;
use App\Http\Resources\BuddhistResource;
use App\Models\Buddhist;

class ShowItemSectionController extends Controller
{
    public function __construct(){

    }

    public function kongDeeCenter(){

        $data = DB::table('notification')->leftJoin("buddhists", "buddhists.id", "=", "notification.buddhist_id")
        ->where([
            ['buddhists.end_time', '<', Carbon::now()],
            ['notification.notification_type', 'bidding_result'],
            ["data", "!=", Auth::id()],
            ["notification.user_id",Auth::id()]
        ])
        ->select("buddhists.id", "buddhists.name", "buddhists.highest_price", "buddhists.image_path", "buddhists.end_time", "buddhists.highBidUser", "buddhists.place")
        ->distinct()
        ->paginate(30);

        $bud = Buddhist::->with('type')->orderBy("created_at", "desc")->paginate(5);
        return BuddhistResource::collection($bud);

        $verifiesData = Verify::with("user")->where([['end_time', '>', Carbon::now()], ["active", "1"]]);
        $items = Buddhist::with("type");
        foreach(Phone::KONGDEE_CENTER_PHONE_NUMBER as $phoneNumber){
            $verifiesData->orWhere("phone")
        }
        $items->get();
        return BuddhistResource::collection($bud); 
    }
}
