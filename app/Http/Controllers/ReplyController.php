<?php

namespace App\Http\Controllers;

use App\Models\Buddhist;
use App\Models\Reply;
use Auth;
use Carbon\carbon;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class ReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        //
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

        $request->validate([
            'message' => 'required|string|max:255',
            'fcm_token' => 'required|string',
            'buddhist_id' => 'required',
            'comment_id' => 'required',
        ]);

        $messaging = app('firebase.messaging');
        $database = app('firebase.database');
        $result = $messaging->validateRegistrationTokens($request->fcm_token);
        if ($result['invalid'] != null) {
            return response()->json(['data' => 'your json token is invalid'], 404);
        }
        $reference = $database->getReference('Comments/' . $request->buddhist_id . '/' . $request->comment_id . '/replies/');
        $data = $reference
            ->orderByChild("uid")
            ->equalTo(Auth::user()->firebase_uid)
            ->limitToFirst(1)
            ->getSnapShot()
            ->getValue();
        $data = $reference->getValue();

        $ownerBuddhist = Buddhist::find($request->buddhist_id);
        $ownerID = $ownerBuddhist->user_id;
        if (empty($data) && Auth::id() != $ownerID) {

            $result = $messaging->subscribeToTopic($ownerBuddhist->comment_topic . $request->comment_id, $request->fcm_token);
        }
        try {
            $database = app('firebase.database');
            $reference = $database->getReference('Comments/' . $request->buddhist_id . '/' . $request->comment_id . '/replies/')
                ->push([
                    'picture' => Auth::user()->getProfilePath(),
                    'uid' => Auth::user()->firebase_uid,
                    'message' => $request->message,
                    'name' => Auth::user()->name,
                    'datetime' => Carbon::now(),
                ]);

            $owner_notification = Notification::fromArray([
                'title' => 'ທ່ານມີແຈ້ງເຕຶອນໃໝ່ຈາກ' . $ownerBuddhist->name . 'ທີ່ທ່ານໄດ້ລົງປະມູນ',
                'body' => $request->message,
                'image' => \public_path("/notification_images/chat.png"),
            ]);
            $owner_notification_data = [
                'buddhist_id' => $request->buddhist_id,
                'comment_id' => $request->comment_id,
                'sender' => Auth::id(),
                "page" => 'listreplies',
            ];
            $owner_message = CloudMessage::withTarget('topic', $ownerBuddhist->topic)
                ->withNotification($owner_notification)
                ->withData($owner_notification_data);
            $messaging->send($owner_message);

            $comment_notification = Notification::fromArray([
                'title' => 'ມີຄົນຕອບກັບຄວາມຄິດເຫັນຂອງເຈົ້າຈາກ' . $ownerBuddhist->name,
                'body' => $request->message,
                'image' => \public_path("/notification_images/chat.png"),
            ]);
            $comment_notification_data = [
                'buddhist_id' => $request->buddhist_id,
                'comment_id' => $request->comment_id,
                'sender' => Auth::id(),
                "page" => 'listreplies',
            ];

            $comment_message = CloudMessage::withTarget('topic', $ownerBuddhist->comment_id . $request->comment_id)
                ->withNotification($comment_notification)
                ->withData($comment_notification_data);
            $messaging->send($comment_message);

            return response()->json(['message' => "successfully"], 200);
        } catch (Exception $e) {
            
            return response()->json(['message' => 'Something went wrong'], 500);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function show(Reply $reply)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function edit(Reply $reply)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:255',
            'buddhist_id' => 'required',
            'comment_id' => 'required',
            'reply_id' => 'required',

        ]);
        try {
            $database = app('firebase.database');
            $reference = $database->getReference('Comments/' . $request->buddhist_id . '/' . $request->comment_id . "/replies/" . $request->reply_id);
            $ownerID = $reference->getSnapShot()->getValue();
            if (empty($ownerID)) {
                return response()->json([
                    "message" => "Data Not Found",
                ]);
            }
            if ($ownerID["uid"] == Auth::user()->firebase_uid) {
                $reference->update([
                    "message" => $request->message,
                ]);
                return response()->json([
                    "message" => "Update your message successfully",
                ], 200);
            } else {
                return Response()->json(['error' => 'You can\'t edit this comment'], 400);
            }

        } catch (Exception $e) {
            return response()->json(
                [
                    'message' => 'Something went wrong',
                ],
                500
            );
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $request->validate([
            "buddhist_id" => "required",
            "comment_id" => "required",
            "reply_id" => "required",
        ]);
        $database = app('firebase.database');
        $reference = $database->getReference('Comments/' . $request->buddhist_id . '/' . $request->comment_id . "/replies/" . $request->reply_id);
        $ownerID = $reference->getSnapShot()->getValue();
        if (empty($ownerID)) {
            return response()->json([
                "message" => "Data Not Found",
            ], 404);
        }

        if ($ownerID["uid"] == Auth::user()->firebase_uid) {

            try {
                $reference = $database->getReference('Comments/' . $request->buddhist_id . '/' . $request->comment_id . '/replies/' . $request->reply_id)->remove();
                return Response()->json(['message' => 'Delete Complete'], 200);
            } catch (Exception $e) {
                return response()->json(
                    [
                        'message' => 'Something went wrong' . $e,
                    ]
                );
            }
        } else {
            return Response()->json(['error' => 'You Can\'t Delete this reply'], 403);
        }
    }
}
