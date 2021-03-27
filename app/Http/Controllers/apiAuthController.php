<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use Firebase\Auth\Token\Exception\InvalidToken;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Response;
use Image;
class apiAuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api')->only(['logOut']);
        
        
    }

    public function login(Request $request)
    {
        $myPathUrl = "http://25.37.239.167";
        $mySecret = "V5PP0aaXT2ITuIMVqIAOtL93e62J8Ck55bcomV9q";
        $myClientID = 2;

        $request->validate([
            'firebase_token' => 'required|string',
            'phone_number' => 'required|numeric',
            'password' => 'required|string',
        ]);
        $auth = app('firebase.auth');
        $idTokenString = $request->input('firebase_token');
        try { // Try to verify the Firebase credential token with Google
            $verifiedIdToken = $auth->verifyIdToken($idTokenString);
        } catch (\InvalidArgumentException $e) { // If the token has the wrong format
            return response()->json([
                'message' => 'Unauthorized - Can\'t parse the token: ' . $e->getMessage(),
            ], 401);
        } catch (InvalidToken $e) { // If the token is invalid (expired ...)
            return response()->json([
                'message' => 'Unauthorized - Token is invalide: ' . $e->getMessage(),
            ], 401);
        }

        $uid = $verifiedIdToken->claims()->get('sub');
        if (User::where('firebase_uid', $uid)->first()) {
            $client = new \GuzzleHttp\Client(
                [
                    'timeout' => 15,
                    
                ]
            );
            if (!Auth::attempt(['phone_number' => $request->phone_number, 'password' => $request->password])) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            try {
                $response = $client->post($myPathUrl . ':8001/oauth/token', [
                    'form_params' => [
                        'grant_type' => 'password',
                        'client_id' => $myClientID,
                        'client_secret' => $mySecret,
                        'username' => $request->phone_number,
                        'password' => $request->password,
                    ],
                ]);
                return json_decode($response->getBody(), true);
                // return $response->json_decode($response);
            } catch (\Guzzle\Exception\BadResponseException $e) {
                if ($e->getCode === 400) {
                    return response()->json("Invalid request. Please enter a username or a password." . $e->getCode());
                } else if ($e->getCode === 401) {
                    return response()->json("Your Credentials are incorrect. Please Try Again" . $e->getCode());
                }
                return response()->json('Something went Wrong on the Server' . $e->getCode());
            }
        } else {
            return response()->json(['message' => 'Can not found the User'], 401);
        }

    }

    public function logOut()
    {
        auth()->user()->tokens->each(function ($token, $key) {
            $token->delete();
        });
        return response()->json('Logout Successfully', 200);
    }

    public function register(Request $request)
    {
        $myPathUrl = "http://25.37.239.167";
        $mySecret = "V5PP0aaXT2ITuIMVqIAOtL93e62J8Ck55bcomV9q";
        $myClientID = 2;
        $request->validate([
            'name' => 'required|max:30|string',
            'surname' => 'required|max:30|string',
            //'email'=>'required|email',
            'phone_number' => 'required|string|unique:users',
            'firebase_token' => 'required|string',
            'password' => 'required|string|min:6|max:18',
            'picture' => 'sometimes|image|mimes:jpeg,png,jpg|max:8192',
            'dob' => 'required|date_format:Y-m-d',
            'village'=>'required|string',
            'city'=>'required|string',
            'province'=>'required|string',
        ]);
        $defaultImage = "default_image.jpg";
        if ($request->hasFile('picture')) {
            $image = $request->file('picture');
            $fileExtension = $image->getClientOriginalExtension();
            $fileName = 'profile_image_' . time() . '.' . $fileExtension;
            $location = public_path("/profile_image/" . $fileName);
            Image::make($image)->resize(460, null, function ($c) {$c->aspectRatio();})->save($location);
            $defaultImage = $fileName;
        }
        $auth = app('firebase.auth');
        $idTokenString = $request->firebase_token;
        try { // Try to verify the Firebase credential token with Google
            $verifiedIdToken = $auth->verifyIdToken($idTokenString);
        } catch (\InvalidArgumentException $e) { // If the token has the wrong format
            return response()->json(
                [
                    'message' => 'Unauthorized - Can\'t parse the token: ' . $e->getMessage(),
                ], 401
            );
        } catch (InvalidToken $e) { // If the token is invalid (expired ...)

            return response()->json([
                'message' => 'Unauthorized - Token is invalid: ' . $e->getMessage(),
            ], 401);
        }

        // Retrieve the UID (User ID) from the verified Firebase credential's token
        $uid = $verifiedIdToken->claims()->get('sub');
        $user = new User();
        $user->name = $request->name;
        $user->surname = $request->surname;
        $user-> phone_number = $request->phone_number;
        $user->  firebase_uid = $uid;
        $user-> password = bcrypt($request->password);
        $user -> picture =$defaultImage;
        $user -> dob = $request->dob;
        $user -> village=$request->village;
        $user -> city=$request->city;
        $user ->province=$request->province;
        

        if ($user->save()) {
            $user->attachRole("bond");
            $http = new \GuzzleHttp\Client([
                'timeout' => 60,
            ]);
            try {
                $response = $http->post($myPathUrl . ':8001/oauth/token', [
                    'form_params' => [
                        'grant_type' => 'password',
                        'client_id' => $myClientID,
                        'client_secret' => $mySecret,
                        'username' => $request->phone_number,
                        'password' => $request->password,
                    ],
                ]);
                return $response->getBody();
            } catch (\GuzzleHttp\Exception\BadResponseException $e) {
                if ($e->getCode() === 400) {
                    return response()->json('Invalid Request. Please enter a Phone number or a password.', $e->getCode());
                } else if ($e->getCode() === 401) {
                    return response()->json('Your credentials are incorrect. Please try again', $e->getCode());
                }

                return response()->json('Something went wrong on the server.', $e->getCode());
            }
        } else {
            return response()->json(['message' => 'Something went Wrong'], 500);
        }

    }

}
