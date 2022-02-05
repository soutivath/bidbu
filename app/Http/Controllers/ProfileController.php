<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserProfile;
use App\Http\Resources\UserProfileWithReview;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Image;
use App\Models\Review;
class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:api")->only(["show", "editProfile"]);
        $this->middleware('isUserActive:api')->only(["show", "editProfile"]);

    }
    public function show()
    {
        $user = User::findOrFail(Auth::id());
        return new UserProfile($user);
    }

    public function getReviewByUserId($id){
        $review = Review::where(["user_id" => $id])->with("review_details.user")->get();
       // return response()->json(["data"=>$review]);
       
        return UserProfileWithReview::collection($review);
        
    }

    public function getUserByID($id)
    {
        $user = User::findOrFail($id);
        return new UserProfile($user);
    }

    public function editProfile(Request $request)
    {
        $request->validate([

            'name' => 'required|max:30|string',
            'surname' => 'required|max:30|string',
            'picture' => 'sometimes|image|mimes:jpeg,png,jpg|max:8192',
            'phone_number' => 'required|string',
            'password' => 'required',
        ]);

        $user = User::find(Auth::id());

        if (!Hash::check($request->password, Auth::user()->password)) {
            return response()->json(["message" => "ລະຫັດຜ່ານບໍ່ຖຶກຕ້ອງ"], 403);
        }

        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->phone_number = $request->phone_number;
        if ($request->hasFile('picture')) {
            if ($user->picture != "default_image.jpg") {
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
}
