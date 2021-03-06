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
use App\Enums\GenderEnum;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class apiAuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api')->only(['logOut', "checkToken","checkVerifyPhoneNumber"]);
    }

    public function getCustomToken(Request $request){
        $request->validate([
            'phone_number' => 'required|string|exists:users,phone_number',
            'password' => 'required|string|min:6|max:18',
        ]);
        if (Auth::attempt(['phone_number' => $request->phone_number, 'password' => $request->password])) {
            $user = User::where("phone_number",$request->phone_number)->first();
            $auth = app('firebase.auth');
            $firebase_uid = $user->firebase_uid;
            $customToken = $auth->createCustomToken($firebase_uid);
            return response()->json(["data"=>$customToken->toString(),"message"=>"get data successfully","success"=>true],200);
        }
        else{
            return response()->json(["data"=>[],"message"=>"unauthorized","success"=>false],401);
        }

     

    }

    public function login(Request $request)
    {

        $request->validate([
            'firebase_token' => 'required|string',
            'phone_number' => 'required|string',
            'password' => 'required|string|min:6|max:18',
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
            if (User::where('firebase_uid', $uid)->first()->active == 0) {
                return response()->json(["message" => "Your account has been shutdown"], 403);
            }
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
                $messaging->subscribeToTopic(\Config::get("values.GLOBAL_BUDDHIST_TOPIC"), $request->fcm_token);
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
        $messaging->unsubscribeFromTopic(\Config::get("values.GLOBAL_BUDDHIST_TOPIC"), $request->fcm_token);


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
                ],
                401
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
            Image::make($image)->resize(460, null, function ($c) {
                $c->aspectRatio();
            })->save($location);
            $defaultImage = $fileName;
        }
        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->phone_number = $request->phone_number;
        $user->firebase_uid = $uid;
        $user->password = bcrypt($request->password);
        $user->picture = $defaultImage;

        $user->topic = "notification_topic_" . $uid . time();

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
                $messaging->subscribeToTopic(\Config::get("values.GLOBAL_BUDDHIST_TOPIC"), $request->fcm_token);

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

    public function forgetPassword(Request $request)
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
                ],
                401
            );
        } catch (InvalidToken $e) { // If the token is invalid (expired ...)

            return response()->json([
                'message' => 'Unauthorized - Token is invalid: ' . $e->getMessage(),
            ], 401);
        }
        $uid = $verifiedIdToken->claims()->get('sub');
        $user = User::where("firebase_uid", $uid)->first();
        if (empty($user)) {
            return response()->json([
                "message" => "User not found",
            ]);
        }
        $user->password = \bcrypt($request->password);
        $user->save();
        return response()->json([
            "message" => "Your password change successfully",
        ], 200);
    }

    public function checkToken()
    {

        return response()->json(["message" => "loggedIn"], 200);
    }


    public function facebook_one_click_login_register(Request $request)
    {
        $request->validate([
            "name" => "required|string",
            "surname" => "required|string",
            "fcm_token" => "required",
            "firebase_token" => "required",
            "picture" => "required|string",
            // "gender" => [
            //     "required",
            //     Rule::in([GenderEnum::MALE, GenderEnum::FEMALE]),
            // ],
            // "date_of_birth" => "required|date",
            "email_address" => "sometimes|email:rfc,dns,filter"
        ]);
        // if($request->has($request->email_address)) {
        //     $checkInEmailField = User::where("email_address", $request->email_address)->first();
        // }
       
      //  $checkInPhoneNumberField = User::where("phone_number", $request->phone_number)->first();
        



        $auth = app('firebase.auth');
        $idTokenString = $request->firebase_token;
        try { // Try to verify the Firebase credential token with Google
            $verifiedIdToken = $auth->verifyIdToken($idTokenString);
        } catch (\InvalidArgumentException $e) { // If the token has the wrong format
            return response()->json(
                [
                    'message' => 'Unauthorized - Can\'t parse the token: ' . $e->getMessage(),
                ],
                401
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
        $http = new \GuzzleHttp\Client([
            'timeout' => 60,
        ]);
        $uid = $verifiedIdToken->claims()->get('sub');
        $password = bcrypt($uid);

        $checkExistingUid  = User::where("firebase_uid",$uid)->first();
        if ($checkExistingUid) {
         $credentialData = "";
         if($request->has("email_address")){
             $credentialData = $request->email_address;
         }else{
             $credentialData = $uid;
         }

            try {
                $response = $http->post(\Config::get("values.APP_URL") . ':' . \Config::get("values.ANOTHER_PORT") . '/oauth/token', [
                    'form_params' => [
                        'grant_type' => 'password',
                        'client_id' => \Config::get("values.CLIENT_ID"),
                        'client_secret' => \Config::get("values.CLIENT_SECRET"),
                        'username' => $credentialData,
                        'password' => $uid,
                    ],
                ]);


                $messaging->subscribeToTopic($checkExistingUid->topic, $request->fcm_token);
                $messaging->subscribeToTopic(\Config::get("values.GLOBAL_BUDDHIST_TOPIC"), $request->fcm_token);

                return $response->getBody();
            } catch (\GuzzleHttp\Exception\BadResponseException $e) {


                if ($e->getCode() === 400) {
                    return response()->json('Invalid Request. Please enter credential.', $e->getCode());
                } else if ($e->getCode() === 401) {
                    return response()->json('Your credentials are incorrect. Please try again', $e->getCode());
                }

                return response()->json('Something went wrong on the server.', $e->getCode());
            }
        }

        // Retrieve the UID (User ID) from the verified Firebase credential's token
        
        $user = new User();


        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->firebase_uid = $uid;
        $user->password = $password;
        $user->picture = $request->picture;
        $user->topic = "notification_topic_" . $uid . time();
       // $user->gender = $request->gender;
       // $user->date_of_birth = Carbon::parse($request->date_of_birth)->format('Y-m-d');

       $credentialLogin = "";
       if($request->has("email_address")){
           $user->email_address = $request->email_address;
           $credentialLogin = $request->email_address;
       }
       else{
           $credentialLogin = $uid;
       }

      
      //  $user->picture = $request->picture;
        if ($user->save()) {
            $user->attachRole("bond");
           
            try {
                $response = $http->post(\Config::get("values.APP_URL") . ':' . \Config::get("values.ANOTHER_PORT") . '/oauth/token', [
                    'form_params' => [
                        'grant_type' => 'password',
                        'client_id' => \Config::get("values.CLIENT_ID"),
                        'client_secret' => \Config::get("values.CLIENT_SECRET"),
                        'username' => $credentialLogin,
                        'password' => $uid,
                    ],
                ]);

                $messaging->subscribeToTopic($user->topic, $request->fcm_token);
                $messaging->subscribeToTopic(\Config::get("values.GLOBAL_BUDDHIST_TOPIC"), $request->fcm_token);

                return $response->getBody();
            } catch (\GuzzleHttp\Exception\BadResponseException $e) {


                if ($e->getCode() === 400) {
                    return response()->json('Invalid Request. Please enter credential.', $e->getCode());
                } else if ($e->getCode() === 401) {
                    return response()->json('Your credentials are incorrect. Please try again', $e->getCode());
                }

                return response()->json('Something went wrong on the server.', $e->getCode());
            }
        } else {
            return response()->json(['message' => 'Something went Wrong'], 500);
        }
    }

    public function checkVerifyPhoneNumber(){
        if(Auth::user()->phone_number==null){
            return response()->json([
                "success" =>false,
                "message"=>"This accound has no phone number please verify your phone number before continuing",
                "data"=>[],
            ],422);
        }
        return response()->json(["data"=>[],"message"=>"checking phone number passed","success"=>true],200);
    }
}
