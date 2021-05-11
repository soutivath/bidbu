<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserProfile;
use Auth;
class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:api")->only("show");

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
