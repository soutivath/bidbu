<?php

namespace App\Http\Controllers;

use App\Models\Buddhist;
use App\Models\comment;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\Notification;
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
    public function store(Request $request, $buddhist_id)
    {

        $request->validate([
            'message' => 'required|string|max:255',
            'fcm_token' => 'required|string',
        ]);
        $messaging = app('firebase.messaging');
        $result = $messaging->validateRegistrationTokens($request->fcm_token);
        if ($result['invalid'] != null) {
            return response()->json(['data' => 'your json token is invalid'], 404);

        }
        $data = comment::where([
            ['user_id', Auth::id()],
            ['buddhist_id', $buddhist_id],
        ])->first();
        $comment = new Comment();
        $comment->message = $request->message;
        $comment->user_id = Auth::user()->id;
        $comment->buddhist_id = $buddhist_id;
        $comment->save();
        if (empty($data)) {
            $topic = "B".$buddhist_id."_C".$comment->id;
            $result = $messaging->subscribeToTopic($topic, $request->fcm_token);
        }
        try {
            $database = app('firebase.database');
            $reference = $database->getReference('Comments/' . $request->buddhist_id . '/' . $comment->id)
                ->set([
                    'comment_id' => $comment->id,
                    'uid' => Auth::user()->firebase_uid,
                    'message' => $request->message,
                    'name' => Auth::user()->name,
                    'datetime' => Carbon::now(),
                    'replies' => '',
                ]);
            $notification = Notification::fromArray([
                'title' => 'ທ່ານມີແຈ້ງເຕຶອນໃໝ່ຈາກ'.Buddhist::find($buddhist_id)->name.'ທີ່ທ່ານໄດ້ລົງປະມູນ',
                'body' => $request->message,
                'image' => \public_path("/notification_images/chat.png"),
            ]);
            $notification_data = [
                'buddhist_id' => $buddhist_id,
                'comment_id' => $comment->id,
            ];
            $message = CloudMessage::withTarget('topic', "B".$buddhist_id)
                ->withNotification($notification)
                ->withData($notification_data);
            $messaging->send($message);
            
            return response()->json(['data' => $comment], 200);
        } catch (Exception $e) {
            $comment->destroy();
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
    public function update(Request $request, $buddhist_id, $comment_id)
    {
        $request->validate([
            'message' => 'required|string|max:255',
        ]);
        $current_comment = Comment::findOrFail($comment_id);
        if ($current_comment->id === Auth::id()) {
            $current_comment->message = $request->message;
            $current_comment->save();
            try {
                $database = app('firebase.database');
                $reference = $database->getReference('Comments/' . $buddhist_id . '/' . $comment_id)
                    ->update(
                        [
                            'message' => $request->message,
                        ]
                    );
                return response()->json([
                    'data' => $current_comment,
                ]);
            } catch (Exception $e) {
                $current_comment->destroy();
                return response()->json(
                    [
                        'message' => 'Something went wrong',
                    ],
                    500
                );
            }
        } else {
            return Response()->json(['error' => 'You Can\'t Edit this Comment'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy($buddhist_id, $comment_id)
    {
        $comment = Comment::findOrFail($comment_id);
        if ($comment->user_id === Auth::id()) {
            $database = app('firebase.database');
            try {
                $reference = $database->getReference('Comments/' . $buddhist_id . '/' . $comment_id)->remove();
                Comment::destroy($comment_id);
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
            return Response()->json(['error' => 'You Can\'t Delete this Comment'], 500);
        }

    }
}
