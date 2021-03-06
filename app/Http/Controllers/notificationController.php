<?php

namespace App\Http\Controllers;

use App\Constants\QueryConstant;
use App\Http\Resources\NotificationResource;
use App\Models\Buddhist;
use App\Models\NotificationFirebase;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class notificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        $this->middleware("auth:api");
        $this->middleware('isUserActive:api');
    }

    public function biddingNotification(Request $request)
    {
        $perPage = QueryConstant::PERPAGE_PAGINATE_DEFAULT;
       
        if($request->has("perPage")){
            $convertedPerPage = (int)$request->perPage;
           


            if($convertedPerPage!=0){
                $perPage = (int)$request->perPage;
            }

            if($convertedPerPage>50){
                $perPage = (int)$request->perPage;
            }
        }
        
        $data = NotificationFirebase::select(['notification.buddhist_id', 'buddhists.name', 'buddhists.image_path', 'notification.data', 'notification.notification_time', 'notification.read', 'notification.comment_path'])
            ->leftJoin('buddhists', 'buddhists.id', '=', 'notification.buddhist_id')
            ->where([['buddhists.end_time', '>', Carbon::now()],
                ["notification.user_id", Auth::user()->id],
                ["notification.notification_type", "bidding_participant"]])
            ->orderBy("notification.created_at", "desc")
            ->paginate($perPage);

        /* $data = NotificationFirebase::where([
        ["user_id", Auth::id()],
        ["notification_type", "bidding_participant"],
        ])->orderBy("created_at", "desc")->get();*/
      /*  if (empty($data)) {
            return response()->json([
                "message" => "no notification",
            ]);
        }*/
        NotificationFirebase::where([
            ["user_id", Auth::id()],
            ["notification_type","bidding_participant"],
            ["read", "0"],
        ])->update([
            'read' => 1,
        ]);

        return NotificationResource::collection($data);
    }

    //get message notification
    public function messageNotification(Request $request)
    {
        $perPage = QueryConstant::PERPAGE_PAGINATE_DEFAULT;
       
        if($request->has("perPage")){
            $convertedPerPage = (int)$request->perPage;
           


            if($convertedPerPage!=0){
                $perPage = (int)$request->perPage;
            }

            if($convertedPerPage>50){
                $perPage = (int)$request->perPage;
            }
        }

        $data = NotificationFirebase::where("user_id", Auth::id())
            ->whereIn("notification_type", ["message_participant", "reply","result_message"])->orderBy("created_at", "desc")->paginate($perPage);
        if (empty($data)) {
            return response()->json([
                "message" => "no notification",
            ]);
        }
        NotificationFirebase::where([
            ["user_id", Auth::id()],
            ["read", "0"],
        ])
        ->whereIn("notification_type", ["message_participant", "reply","result_message"])
        ->update([
            'read' => 1,
        ]);

        return NotificationResource::collection($data);

    }

    public function biddingResultNotification(Request $request)
    {

        $perPage = QueryConstant::PERPAGE_PAGINATE_DEFAULT;
       
        if($request->has("perPage")){
            $convertedPerPage = (int)$request->perPage;
           


            if($convertedPerPage!=0){
                $perPage = (int)$request->perPage;
            }

            if($convertedPerPage>50){
                $perPage = (int)$request->perPage;
            }
        }
        

        $data = NotificationFirebase::where([
            ["user_id", Auth::id()],
            //  ["notification_type", "bidding_result"],
        ])
            ->whereIn("notification_type", ["bidding_result", "owner_result"])
            ->orderBy("created_at", "desc")->paginate($perPage);
        if (empty($data)) {
            return response()->json([
                "message" => "no notification",
            ]);
        }
        NotificationFirebase::where([
            ["user_id", Auth::id()],
            ["read", "0"],
        ])
        ->whereIn("notification_type", ["bidding_result", "owner_result"])
        ->update([
            'read' => 1,
        ]);

        return NotificationResource::collection($data);

    }

    public function unreadBiddingCount()
    {
        $data = NotificationFirebase::where([
            ["user_id", Auth::id()],
            ["read", "0"],
            ["notification_type", "bidding_participant"],
        ])->orderBy("created_at", "desc")->get();
        return response()->json([
            "notification_count" => $data->count(),
        ]);
    }
    public function unreadMessageCount()
    {
        $data = NotificationFirebase::where([["user_id", Auth::id()], ["read", "0"]],
        )->whereIn("notification_type", ["message_participant","result_message"], )->orderBy("created_at", "desc")->get();
        return response()->json([
            "notification_count" => $data->count(),
        ]);
    }
    public function unReadBiddingResult()
    {
        $data = NotificationFirebase::where([
            ["user_id", Auth::id()],
            ["read", "0"],
            ["notification_type", "bidding_result"]])->orderBy("created_at", "desc")->get();
        return response()->json([
            "notification_count" => $data->count(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\NotificationFirebase  $NotificationFirebase
     * @return \Illuminate\Http\Response
     */
    public function show(NotificationFirebase $NotificationFirebase)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\NotificationFirebase  $NotificationFirebase
     * @return \Illuminate\Http\Response
     */
    public function edit(NotificationFirebase $NotificationFirebase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NotificationFirebase  $NotificationFirebase
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NotificationFirebase $NotificationFirebase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\NotificationFirebase  $NotificationFirebase
     * @return \Illuminate\Http\Response
     */
    public function destroy(NotificationFirebase $NotificationFirebase)
    {
        //
    }

    public function deleteNotification(Request $request)
    {
        try {
            $request->validate([
                "fcm_token" => "required",
                "notification_type" => "required",
                "buddhist_id" => "required",
            ]);
            $data = NotificationFirebase::where([
                ["user_id", "=", Auth::id()],
                ["buddhist_id", $request->buddhist_id],
                ["notification_type", "$request->notification_type"],
            ]);

            $messaging = app("firebase.messaging");
            $messaging->unsubscribeFromTopic($data->buddhist->topic, $request->fcm_token);
            $data->delete();
            return response()->json(["message" => "Delete notification complete"], 200);
        } catch (Exception $e) {
            return response()->json(["message" => "Server Error"], 500);
        }

    }

}
