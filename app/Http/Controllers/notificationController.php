<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\NotificationFirebase;
use Auth;
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
    }

    public function biddingNotification()
    {

        $data = NotificationFirebase::where([
            ["user_id", Auth::id()],
            ["notification_type", "bidding"],
        ])->orderBy("created_at", "desc")->get();
        if (empty($data)) {
            return response()->json([
                "message" => "no notification",
            ]);
        }
        NotificationFirebase::where([
            ["user_id", Auth::id()],
            ["read", "0"],
        ])->update([
            'read' => 1,
        ]);

        return NotificationResource::collection($data);
    }

    //get message notification
    public function messageNotification()
    {
        $data = NotificationFirebase::where("user_id", Auth::id())
            ->whereIn("notification_type", ["message", "reply"])->orderBy("created_at", "desc")->get();
        if (empty($data)) {
            return response()->json([
                "message" => "no notification",
            ]);
        }
        NotificationFirebase::where([
            ["user_id", Auth::id()],
            ["read", "0"],
        ])->update([
            'read' => 1,
        ]);

        return NotificationResource::collection($data);

    }

    public function biddingResultNotification()
    {
        $data = NotificationFirebase::where([
            ["user_id", Auth::id()],
            ["notification_type", "bidding_result"],
        ])->orderBy("created_at", "desc")->get();
        if (empty($data)) {
            return response()->json([
                "message" => "no notification",
            ]);
        }
        NotificationFirebase::where([
            ["user_id", Auth::id()],
            ["read", "0"],
        ])->update([
            'read' => 1,
        ]);

        return NotificationResource::collection($data);

    }

    public function unreadBiddingCount()
    {
        $data = NotificationFirebase::where([
            ["user_id", Auth::id()],
            ["read", "0"],
            ["notification_type", "bidding"],
        ])->orderBy("created_at", "desc")->get();
        return response()->json([
            "notification_count" => $data->count(),
        ]);
    }
    public function unreadMessageCount()
    {
        $data = NotificationFirebase::where([["user_id", Auth::id()], ["read", "0"]],
        )->whereIn("notification_type", ["message", "reply"], )->orderBy("created_at", "desc")->get();
        return response()->json([
            "notification_count" => $data->count(),
        ]);
    }
    public function unReadBiddingResult()
    {
        $data = NotificationFirebase::where([
            ["user_id", Auth::id()], 
            ["read", "0"], 
            ["notification_type", "bidding_result"]] )->orderBy("created_at", "desc")->get();
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
}
