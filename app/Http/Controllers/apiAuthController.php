<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use File;
use Firebase\Auth\Token\Exception\InvalidToken;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Image;
use Response;

class apiAuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api')->only(['logOut', 'forgetPassword']);

    }

    public function login(Request $request)
    {

        $request->validate([
            'firebase_token' => 'required|string',
            'phone_number' => 'required|numeric',
            'password' => 'required|string',
            'fcm_token' => 'required|string',
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
                    'timeout' => 60,
                ]
            );
            if (!Auth::attempt(['phone_number' => $request->phone_number, 'password' => $request->password])) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            try {
                $response = $client->post(\Config::get("values.APP_URL") . ':' . \Config::get("values.ANOTHER_PORT") . '/oauth/token', [
                    'form_params' => [
                        'grant_type' => 'password',
                        'client_id' => \Config::get("values.CLIENT_ID"),
                        'client_secret' => \Config::get("values.CLIENT_SECRET"),
                        'username' => $request->phone_number,
                        'password' => $request->password,
                    ],
                ]);
                $messaging = app('firebase.messaging');
                $result = $messaging->validateRegistrationTokens($request->fcm_token);
                if ($result['invalid'] != null) {
                    return response()->json(['data' => 'your json token is invalid'], 404);

                }

                $messaging->subscribeToTopic(Auth::user()->topic, $request->fcm_token);

                return $response->getBody();

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
            return response()->json(['message' => 'User not found'], 404);
        }

    }

    public function logOut(Request $request)
    {
        $request->validate([
            "fcm_token" => "required",
        ]);
        $messaging = app('firebase.messaging');
        $result = $messaging->validateRegistrationTokens($request->fcm_token);
        if ($result['invalid'] != null) {
            return response()->json(['data' => 'your json token is invalid'], 404);

        }

        $messaging->unsubscribeFromTopic(Auth::user()->topic, $request->fcm_token);

        auth()->user()->tokens->each(function ($token, $key) {
            $token->delete();
        });
        return response()->json('Logout Successfully', 200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|max:30|string',
            'surname' => 'required|max:30|string',
            //'email'=>'required|email',
            'phone_number' => 'required|string|unique:users',
            'firebase_token' => 'required|string',
            'password' => 'required|string|min:6|max:18',
            'picture' => 'sometimes|image|mimes:jpeg,png,jpg|max:8192',
            'dob' => 'required|date_format:Y-m-d',
            'village' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'fcm_token' => 'required|string',
        ]);

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

        $messaging = app('firebase.messaging');
        $result = $messaging->validateRegistrationTokens($request->fcm_token);
        if ($result['invalid'] != null) {
            return response()->json(['data' => 'your json token is invalid'], 404);

        }

        // Retrieve the UID (User ID) from the verified Firebase credential's token
        $uid = $verifiedIdToken->claims()->get('sub');
        $user = new User();
        $defaultImage = "default_image.jpg";
        $location = "";
        if ($request->hasFile('picture')) {
            $image = $request->file('picture');
            $fileExtension = $image->getClientOriginalExtension();
            $fileName = 'profile_image_' . time() . '.' . $fileExtension;
            $location = public_path("/profile_image/" . $fileName);
            Image::make($image)->resize(460, null, function ($c) {$c->aspectRatio();})->save($location);
            $defaultImage = $fileName;
        }
        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->phone_number = $request->phone_number;
        $user->firebase_uid = $uid;
        $user->password = bcrypt($request->password);
        $user->picture = $defaultImage;
        $user->dob = $request->dob;
        $user->village = $request->village;
        $user->city = $request->city;
        $user->province = $request->province;
        $user->topic = "notification_topic_" . $uid;

        if ($user->save()) {
            $user->attachRole("bond");
            $http = new \GuzzleHttp\Client([
                'timeout' => 60,
            ]);
            try {
                $response = $http->post(\Config::get("values.APP_URL") . ':' . \Config::get("values.ANOTHER_PORT") . '/oauth/token', [
                    'form_params' => [
                        'grant_type' => 'password',
                        'client_id' => \Config::get("values.CLIENT_ID"),
                        'client_secret' => \Config::get("values.CLIENT_SECRET"),
                        'username' => $request->phone_number,
                        'password' => $request->password,
                    ],
                ]);

                $messaging->subscribeToTopic($user->topic, $request->fcm_token);

                return $response->getBody();

            } catch (\GuzzleHttp\Exception\BadResponseException $e) {
                File::delete($location);

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

    public function forgetPassword()
    {
        $request->validate([
            'firebase_token' => 'required|string',
            'password' => 'required|string|min:6|max:18',
        ]);

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
        $uid = $verifiedIdToken->claims()->get('sub');
        $user = User::where("firebase_uid", $uid)->get();
        if (empty($user)) {
            return response()->json([
                "message" => "User not found",
            ]);
        }
        $user->password = \bcrypt($request->password);
        $user->save();
        return response()->json([
            "message" => "Your password change successfully",
        ]);

    }

}
