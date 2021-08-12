<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserProfile;
use App\Models\User;
use Auth;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:api")->only("show");
        $this->middleware('isUserActive:api')->only("show");

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
            'user_id' => "required",
            'name' => 'sometimes|max:30|string',
            'surname' => 'sometimes|max:30|string',
            'picture' => 'sometimes|image|mimes:jpeg,png,jpg|max:8192',
        ]);
        $user = User::find($request->user_id);

        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('surname')) {
            $user->surname = $request->surname;
        }
        if ($request->hasFile('picture')) {
            $oldPath = public_path() . '/profile_image/' . $user->picture;
            if (\File::isDirectory($path)) {
                \File::deleteDirectory($path);
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
