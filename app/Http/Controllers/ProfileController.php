<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:api");

    }
    public function show()
    {
        $user = User::findOrFail(Auth::id());
        return response()->json(['data',$user],200);
    }
}
