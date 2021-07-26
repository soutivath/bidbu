<?php

namespace App\Http\Controllers;

use App\Models\Buddhist;
use App\Models\comment;
use App\Models\NotificationFirebase;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('isUserActive:api');

    }
    public function index()
    {
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
            'buddhist_id' => 'required|string',
        ]);
        try {
            $database = app('firebase.database');
            $messaging = app('firebase.messaging');
            $result = $messaging->validateRegistrationTokens($request->fcm_token);
            if ($result['invalid'] != null) {
                return response()->json(['data' => 'your json token is invalid'], 404);

            }
            // search data
            $reference = $database->getReference('Comments/' . $request->buddhist_id . '/')
                ->orderByChild("uid")
                ->equalTo(Auth::user()->firebase_uid)
                ->limitToFirst(1)
                ->getSnapshot();
            $data = $reference->getValue();

            //sub to topic
            $ownerBuddhist = Buddhist::find($request->buddhist_id);
            $ownerID = $ownerBuddhist->user->id;

            $reference = $database->getReference('Comments/' . $request->buddhist_id . '/')
                ->push([
                    'picture' => Auth::user()->getProfilePath(),
                    'uid' => Auth::user()->firebase_uid,
                    'message' => $request->message,
                    'name' => Auth::user()->name,
                    'datetime' => Carbon::now(),
                    'replies' => '',
                ]);
            $comment_id = $reference->getKey();

            if (empty($data) && Auth::id() != $ownerID) {

                $result = $messaging->subscribeToTopic($ownerBuddhist->comment_topic, $request->fcm_token);
            }

            /*  $owner_notification = Notification::fromArray([
            'title' => 'ຄວາມຄິດເຫັນໃໝ່ຈາກ ' . $ownerBuddhist->name . ' ທີ່ທ່ານໄດ້ປ່ອຍ',
            'body' => $request->message,
            'image' => \public_path("/notification_images/chat.png"),
            ]);
            $owner_notification_data = [
            'sender' => Auth::id(),
            'buddhist_id' => $request->buddhist_id,
            'comment_id' => $comment_id,
            'page' => 'content_detail',
            ];
            $owner_message = CloudMessage::withTarget('topic', $ownerBuddhist->user->topic)
            ->withNotification($owner_notification)
            ->withData($owner_notification_data);
            $messaging->send($owner_message);*/
            $owner_message = CloudMessage::withTarget('topic', $ownerBuddhist->user->topic)
                ->withNotification([
                    'title' => 'ຄວາມຄິດເຫັນໃໝ່ຈາກ ' . $ownerBuddhist->name . ' ທີ່ທ່ານໄດ້ປ່ອຍ',
                    'body' => $request->message,
                    'image' => \public_path("/notification_images/chat.png"),
                ])
                ->withData([
                    'sender' => Auth::id(),
                    'buddhist_id' => $request->buddhist_id,
                    'comment_id' => $comment_id,
                    'page' => 'content_detail',
                ]);
            $messaging->send($owner_message);

            //*******/
            /*  $comment_notification = Notification::fromArray([
            'title' => 'ຄວາມຄິດເຫັນໃຫມ່ຈາກ ' . $ownerBuddhist->name,
            'body' => $request->message,
            'image' => \public_path("/notification_images/chat.png"),
            ]);
            $comment_notification_data = [
            'sender' => Auth::id(),
            'buddhist_id' => $request->buddhist_id,
            'comment_id' => $comment_id,
            'page' => 'content_detail',
            ];
            $comment_message = CloudMessage::withTarget('topic', $ownerBuddhist->comment_topic)
            ->withNotification($comment_notification)
            ->withData($comment_notification_data);
            $messaging->send($comment_message);*/
            $comment_message = CloudMessage::withTarget('topic', $ownerBuddhist->comment_topic)
                ->withNotification([
                    'title' => 'ຄວາມຄິດເຫັນໃຫມ່ຈາກ ' . $ownerBuddhist->name,
                    'body' => $request->message,
                    'image' => \public_path("/notification_images/chat.png"),
                ])
                ->withData([
                    'sender' => Auth::id(),
                    'buddhist_id' => $request->buddhist_id,
                    'comment_id' => $comment_id,
                    'page' => 'content_detail',
                ]);
            $messaging->send($comment_message);

            NotificationFirebase::create([
                'notification_time' => date('Y-m-d H:i:s'),
                'read' => 1,
                'data' => $request->message,
                'buddhist_id' => $request->buddhist_id,
                'user_id' => Auth::id(),
                'notification_type' => "message_participant",
                'comment_path' => 'Comments/' . $request->buddhist_id . '/' . $comment_id,

            ]);

            if (Auth::id() != $ownerID) {
                $notificationData = NotificationFirebase::
                    where([
                    ["buddhist_id", $request->buddhist_id],
                    ["notification_type", "message_participant"],
                    ["user_id", "!=", Auth::id()],
                ])->select("user_id")->distinct()->get();

                for ($i = 0; $i < count($notificationData); $i++) {
                    NotificationFirebase::create([
                        'notification_time' => date('Y-m-d H:i:s'),
                        'read' => 0,
                        'data' => $request->message,
                        'buddhist_id' => $request->buddhist_id,
                        'user_id' => $notificationData[$i]["user_id"],
                        'notification_type' => "message",
                        'comment_path' => 'Comments/' . $request->buddhist_id . '/' . $comment_id,

                    ]);

                }
            }

            return response()->json([
                "data" => $reference->getValue(),
            ], 201);
        } catch (Exception $e) {
            return response()->json(['Message' => 'Something went wrong'], 500);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function edit(comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:255',
            'buddhist_id' => 'required',
            'comment_id' => 'comment_id',
        ]);
        try {
            $database = app('firebase.database');
            $reference = $database->getReference('Comments/' . $request->buddhist_id . '/' . $request->comment_id);
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
                    "message" => "Message updated successfully",
                ], 200);
            } else {
                return Response()->json(['error' => 'You can\'t edit this comment'], 403);
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
     * @param  \App\Models\comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $request->validate([
            "buddhist_id" => "required",
            "comment_id" => "required",
        ]);
        $database = app('firebase.database');
        $reference = $database->getReference('Comments/' . $request->buddhist_id . '/' . $request->comment_id);
        $ownerID = $reference->getSnapShot()->getValue();
        if (empty($ownerID)) {
            return response()->json([
                "message" => "Data Not Found",
            ], 404);
        }
        if ($ownerID["uid"] == Auth::user()->firebase_uid) {
            $database = app('firebase.database');
            try {
                $reference = $database->getReference('Comments/' . $request->buddhist_id . '/' . $request->comment_id)->remove();
                return Response()->json(['message' => 'Delete Complete'], 200);
            } catch (Exception $e) {
                return response()->json(
                    [
                        'message' => 'Something went wrong',
                    ],
                    500
                );
            }
        } else {
            return Response()->json(['error' => 'You Can\'t Delete this Comment'], 400);
        }

    }
}
