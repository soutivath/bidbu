<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;
class GetToken extends Controller
{

    public function getToken()
    {
       

        $user = User::findOrFail(3);
       
        $token = $user->createToken('Password grant client')->accessToken;
        return response()->json([
            "token"=>$token
        ]);
    }
    public function getTokenUser()
    {
       

        $user = User::findOrFail(1);
       
        $token = $user->createToken('Password grant client')->accessToken;
        return response()->json([
            "token"=>$token
        ]);
    }

    public function getReview($id)
    {
        $review = Review::where(["user_id" => $id])->with("review_details.user")->get()->pluck("review_details");
        return response()->json(["data"=>$review]);
    }
}
