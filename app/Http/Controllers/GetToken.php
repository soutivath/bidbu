<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class GetToken extends Controller
{

    public function getToken()
    {
        $user = User::findOrFail(1);
        $token = $user->createToken('Password grant client')->accessToken;
        return response()->json([
            "token"=>$token
        ]);
    }
}
