<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserProfile;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

    public function getUserByID($id)
    {
        $user = User::findOrFail($id);
        return new UserProfile($user);
    }

    public function editProfile(Request $request)
    {
        $request->validate([

            'name' => 'sometimes|max:30|string',
            'surname' => 'sometimes|max:30|string',
            'picture' => 'sometimes|image|mimes:jpeg,png,jpg|max:8192',
            'password' => 'required',
        ]);

        $user = User::find(Auth::id());

        if (!Hash::check($request->password, Auth::user()->password)) {
            return response()->json(["message" => "incorrect "], 403);
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('surname')) {
            $user->surname = $request->surname;
        }
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

        $bud->save();
        return response()->json(["message" => "update data complete"], 200);

    }
}
