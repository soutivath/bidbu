<?php

namespace App\Http\Controllers;

use App\Models\comment;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
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
    public function store(Request $request,$buddhist_id)
    {
        $request->validate([
            'message'=>'required|string|max:255',
        ]);
        $comment = new Comment();
        $comment->message = $request->message;
        $comment->user_id = Auth::user()->id;
        $comment->buddhist_id=$buddhist_id;
        $comment->save();
        try{
            $database = app('firebase.database');
            $reference = $database->getReference('Comments/'.$request->buddhist_id.'/'.$comment->id)
            ->set([
                'comment_id'=>$comment->id,
                'uid'=>Auth::user()->firebase_uid,
                'message'=>$request->message,
                'name'=>Auth::user()->name,
                'datetime'=>Carbon::now(),
            ]);
            return response()->json(['data'=>$comment],200);
        }catch(Exception $e)
        {
            $comment->destroy();
            return response()->json(['Message'=>'Something went wrong'.$e],500);
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
    public function update(Request $request, $buddhist_id,$comment_id)
    {
        $request->validate([
            'message'=>'required|string',
        ]);
        $current_comment = Comment::findOrFail($comment_id);
        if($current_comment->id===Auth::id())
        {
            $current_comment->message= $request->message;
            $current_comment->save();
       try{
           $database = app('firebase.database');
           $reference = $database->getReference('Comments/'.$buddhist_id.'/'.$comment_id)
           ->update(
               [
                   'message'=>$request->message
               ]
           );
           return response()->json([
               'data'=>$current_comment
           ]);
        }
        catch(Exception $e)
        {
            $current_comment->destroy();
            return response()->json(
                [
                    'message'=>'Something went wrong'.$e
                ]
                );
        }
    }else{
        return Response()->json(['error'=>'You Can\'t Edit this Comment']);
    }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy($buddhist_id,$comment_id)
    {
        $comment = Comment::findOrFail($comment_id);
        if($comment->user_id===Auth::id()){
            $database = app('firebase.database');
            try{
                $reference = $database->getReference('Comments/'.$buddhist_id.'/'.$comment_id)->remove();
                Comment::destroy($comment_id);
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
            return Response()->json(['error'=>'You Can\'t Delete this Comment']);
        }
       
       
    }
}
