<?php

namespace App\Http\Controllers;

use App\Http\Resources\BuddhistResource;
use App\Http\Resources\participantBiddingResource;
use App\Http\Resources\UserProfile;
use App\Http\Resources\UserProfileWithReview;
use App\Models\Buddhist;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Image;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:api")->only(["show", "editProfile"]);
        $this->middleware('isUserActive:api')->only(["show", "editProfile"]);

    }
    public function show()
    {
        $user = User::where("id",Auth::id())->with("verify")->first();
   
        return new UserProfile($user);
    }

    public function getReviewByUserId($id){
        $review = Review::where(["user_id" => $id])->with("review_details.user")->first();
       // return response()->json(["data"=>$review]);
       if(!$review){
           return response()->json([
               "data"=>[
                "id"=>0,
                "all_star"=>0,
                "reviews"=>[]
               ],
           ]);
       }
        return new UserProfileWithReview($review);
        
    }

    public function getUserByID($id)
    {
        $user = User::where("id",$id)->with("verify")->first();
        if(!$user){
            return response()->json(["data"=>[],"message"=>"User not found",404]);
        }
        return new UserProfile($user);
    }

    public function editProfile(Request $request)
    {
        $request->validate([

            // 'name' => 'required|max:30|string',
            // 'surname' => 'required|max:30|string',
           'picture' => 'required|image|mimes:jpeg,png,jpg|max:8192',
           // 'phone_number' => 'required|string',
          //  'password' => 'required',
        ]);

        $user = User::find(Auth::id());

        /*if (!Hash::check($request->password, $user->password)) {
            return response()->json(["message" => "ລະຫັດຜ່ານບໍ່ຖຶກຕ້ອງ"], 403);
        }*/

        // $user->name = $request->name;
        // $user->surname = $request->surname;
        // $user->phone_number = $request->phone_number;
        if ($request->hasFile('picture')) {
           
            if ($user->picture != "default_image.jpg" && !str_starts_with($user->picture,"https://")) {
                $oldPath = public_path() . '/profile_image/' . $user->picture;
                \unlink($oldPath);

            }

            $image = $request->file('picture');
            $fileExtension = $image->getClientOriginalExtension();
            $fileName = 'profile_image_' . time() . '.' . $fileExtension;
            $location = public_path("/profile_image/" . $fileName);
            Image::make($image)->resize(460, null, function ($c) {$c->aspectRatio();})->save($location);
            $user->picture = $fileName;
        }

        $user->save();
        return response()->json(["message" => $user], 200);

    }

    public function itemBelongToUser($id){
        $item = Buddhist::where("user_id",$id)->get();
        return BuddhistResource::collection($item);
    }

    public function userItemParticipant($id){
        $data = DB::table('notification')->leftJoin("buddhists", "buddhists.id", "=", "notification.buddhist_id")
            ->where([
                ["notification.user_id",$id],
            ])
            ->whereIn("notification_type", ["bidding_participant"])
            ->select("buddhists.id", "buddhists.name", "buddhists.highest_price", "buddhists.image_path", "buddhists.end_time", "buddhists.highBidUser", "buddhists.place")
            ->distinct()
            ->paginate(30);

        return participantBiddingResource::collection($data);
    }
}
