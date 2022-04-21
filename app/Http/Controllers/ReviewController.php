<?php

namespace App\Http\Controllers;

use App\Http\Requests\Review\PostUpdateRequest;
use App\Models\Review;
use App\Models\ReviewDetail;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Http\Request;
use App\Models\Buddhist;
use App\Models\User;
use Carbon\carbon;
use Illuminate\Support\Facades\Auth;
class ReviewController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('isUserActive:api');
    }
    public function store(PostUpdateRequest $request)
    {
        if(Auth::id()==$request->user_id)
        {
            return response()->json([
                "message"=>"You cannot review yourself"
            ],400);
        }
        
        $buddhist = Buddhist::find($request->buddhist_id);
       
        if(!$buddhist){
            return response()->json([
                "data"=>[],
                "success"=>false,
                "message"=>"Item not found"
            ],404);
        }

        if($buddhist->user_id==$buddhist->highBidUser){
            return response()->json([
                "data"=>[],
                "success"=>false,
                "message"=>"No participant can't write a review"
            ],400);
        }


        $winnerUser = User::findOrFail($buddhist->highBidUser);
        return response()->json([
            "current_auth_id"=>Auth::id(),
            "winner_id"=>$winnerUser->id,
            "owner_id"=>$buddhist->user_id
        ]);
        if(Carbon::now()->lessThan(Carbon::now()->parse($buddhist->end_time)))
        {
            return response()->json(["message"=>"This item not expired yet."],400);
        }
     

        if(Auth::id()!=$winnerUser->id && Auth::id()!=$buddhist->user_id )
        {
            return response()->json(["message"=>"You are not a winner or owner."],400);
        }


        $review= Review::firstOrCreate(['user_id'=> $request->user_id],[]);
        $checkExistingComment = ReviewDetail::where([
            ["user_id",Auth::id()],
            ["buddhist_id",$request->buddhist_id],
            ["review_id",$review->id]
        ])->first();
        if($checkExistingComment)
        {
            return response()->json([
                "message"=>"You already post the comment try updating the existing one"
            ]);
        }
      
        $comment = "";
        if($request->has("comment")){
            $comment = $request->comment;
        }
       // id	score	comment review_id	user_id buddhist_id
        $reviewDetail = new ReviewDetail();
        $reviewDetail->score = $request->score;
        $reviewDetail->comment = $comment;
        $reviewDetail->review_id = $review->id;
        $reviewDetail->user_id = Auth::id();
        $reviewDetail->buddhist_id = $request->buddhist_id;
        $reviewDetail->save();

        return response()->json(["message"=>"Review successfully"],201);

    }

    public function update(PostUpdateRequest $request)
    {
         // id	score	comment review_id	user_id
         if($request->user_id==Auth::id())
         {
             return response()->json([
                 "message"=>"don't try review yourself"
             ]);
         }
         $theReview = Review::where("user_id",$request->user_id)->first();
         if(!$theReview)
         {
             return response()->json([
                 "message"=>"no record found"
             ],404);
         }
         $review = ReviewDetail::where([
             [
                 'user_id',Auth::id(),
                 'buddhist_id' =>$request->buddhist_id
             ],
             [
                 'review_id',$theReview->id
             ]
         ])->first();
         if($review)
         {
            $comment = "";
            if($request->has("comment")){
                $comment = $request->comment;
            }
            $review->score = $request->score;
            $review->comment = $comment;
            $review->save();
            return response()->json(["message"=>"Update comment successfully"],200);
         }
         else{
             return response()->json(["message"=>"Comment not found"],404);
         }

    }

    /**
     * @return JOSN
     */
    public function destroy($user_id)
    {
        $ownerReview = Review::where("user_id",$user_id)->first();
        if(!$ownerReview)
        {
            return response()->json([
                "message"=>"no record found"
            ]);
        }

        $review = ReviewDetail::where([
            ["user_id",Auth::id()],
            ["review_id",$ownerReview->id]
        ])->first();
        if(!$review)
        {
            return response()->json([
                "message"=>"Data not found"
            ],404);
        }
        $review->delete();
        return response()->json(["data"=>$review]);
    }


    /**
     * @param $user_id
     * @return JSON
     */
    public function getReview($user_id)
    {
        $reviews = Review::where("user_id",$user_id)->with(["review_details"=>function($query){
            $query->with(["user"=>function($query){
                $query->select("id","name","surname");
            }])->select("score","comment","review_id","user_id");
        }])->select("id","user_id")->paginate(30);
        $reviewRating = ReviewDetail::avg("score");
        $ownerReview = ReviewDetail::where("user_id",Auth::id())->select("id","score","comment")->get();
        return response()->json([
            "success"=>true,
            "reviews"=>$reviews,
            "ownerReview"=>$ownerReview,
            "rating"=>$reviewRating
        ]);
    }






}
