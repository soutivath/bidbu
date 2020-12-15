<?php

namespace App\Http\Controllers;

use App\Models\Reply;
use Illuminate\Http\Request;
use Auth;
use Carbon\carbon;
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
    public function store(Request $request,$buddhist_id,$comment_id)
    {
        
        $request->validate([
            'message'=>'required|string',
        ]);
        $reply = new Reply();
        $reply->message=$request->message;
        $reply->user_id = Auth::user()->id;
        $reply->comment_id= $comment_id;
        $reply->save();
        try{
            $database = app('firebase.database');
            $reference = $database->getReference('Comments/'.$buddhist_id.'/'.$comment_id.'/replies/'.$reply->id)
            ->set([
                'reply_id'=>$reply->id,
                'uid'=>Auth::user()->firebase_uid,
                'message'=>$request->message,
                'name'=>Auth::user()->name,
                'datetime'=>Carbon::now(),
            ]);
            return response()->json(['data'=>$reply],200);
        }catch(Exception $e)
        {
            $reply->destroy();
            return response()->json(['message'=>'Something went wrong'],500);
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
    public function update(Request $request,$buddhist_id,$comment_id,$reply_id)
    {
        $request->validate([
            'message'=>'required|string',
        ]);
        $reply = Reply::findOrFail($reply_id);
        if(Auth::id()==$reply->user_id)
        {
            $reply->message = $request->message;
        $reply->save();
        try{
            $database = app('firebase.database');
            $reference = $database->getReference('Comments/'.$buddhist_id.'/'.$comment_id.'/replies/'.$reply->id)
            ->update([
                'message'=>$request->message
            ]);
            return Response()->json(['message'=>'update reply successfully'],200);
        }catch(Exception $e)
        {
            $reply->destroy();
            return Response()->json(['error'=>'something went wrong'],500);
        }
        }
        else{
            return Response()->json(['error'=>'You Can\'t Update this reply']);
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function destroy($buddhist_id,$comment_id,$reply_id)
    {
        $reply = Reply::findOrFail($reply_id);
        if($reply->user_id===Auth::id()){
            $database = app('firebase.database');
            try{
                $reference = $database->getReference('Comments/'.$buddhist_id.'/'.$comment_id.'/replies/'.$reply_id)->remove();
                Reply::destroy($reply_id);
                return Response()->json(['message'=>'Delete Complete'],200);
            }catch(Exception $e)
            {
                return response()->json(
                    [
                        'message'=>'Something went wrong'.$e
                    ]
                    );
            }
        }
        else{
            return Response()->json(['error'=>'You Can\'t Delete this reply']);
        }
    }
}
