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
}
