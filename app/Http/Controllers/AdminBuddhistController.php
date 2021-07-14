<?php

namespace App\Http\Controllers;

use App\Http\Resources\Admin\AdminBuddhistResource;
use App\Http\Resources\Admin\AdminUserResource;
use App\Models\Buddhist;
use App\Models\User;
use Auth;
use carbon\Carbon;
use Firebase\Auth\Token\Exception\InvalidToken;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Image;

class AdminBuddhistController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:api")->except("login");
    }

    public function index()
    {
        $buddhist = Buddhist::where('active', '1')->orderBy('created_at', 'desc')->with(["type", "user"])->get();

        return AdminBuddhistResource::collection($buddhist);
    }

    public function getActive()
    {
        $buddhist = Buddhist::where('end_time', '>', Carbon::now())->where('active', '1')->with('type')->orderBy("created_at", "desc")->get();

        return AdminBuddhistResource::collection($buddhist);

    }

    public function getNonActive()
    {
        $buddhist = Buddhist::where('end_time', '<=', Carbon::now())->where('active', '1')->with('type')->orderBy("created_at", "desc")->get();

        return AdminBuddhistResource::collection($buddhist);

    }
    public function getAllUser()
    {
        $user = User::whereRoleIs(["bond", "premium", "gold"])->get();
        return AdminUserResource::collection($user);
    }

    public function getUserByID(Request $request, $userID)
    {
        $user = User::find($userID);
        if ($user != null) {
            return new AdminUserResource($user);
        } else {
            return response()->json(["message" => "User not found"], 404);
        }
    }

    public function disableBuddhist(Request $request)
    {
        $request->validate([
            "id" => "required|integer",
        ]);
        $buddhist = Buddhist::find($request->id);
        if ($buddhist != null) {
            $buddhist->active = 0;
            $buddhist->save();
            return response()->json(["message" => "Disable buddhist successfully"], 200);
        } else {
            return response()->json(["message" => "This is not found"], 404);
        }
    }

    public function getDisableBuddhist()
    {
        $buddhist = Buddhist::where("active", "0")->with('type')->orderBy("created_at", "desc")->get();
        return AdminBuddhistResource::collection($buddhist);
    }

    public function disableUser(Request $request)
    {

        $request->validate([
            "id" => "required|integer",
        ]);
        if (Auth::id() === $request->id) {
            return response()->json(["message" => "ບໍ່ສາມາດປິດບັນຊີທີ່ໃຊ້ງານຢູ່ໄດ້"], 405);
        }
        try {
            $user = User::findOrFail($request->id)->whereRoleIs("admin")->get();
            $user->active = "0";
            $user->save();
            return response()->json([
                "message" => "ປິດໃຊ້ງານບັນຊີຂອງ" . $user->name . "ສຳເລັດແລ້ວ",
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(["message" => "User not found"], 404);
        }

    }

    public function getAdminRole()
    {
        $allAdmin = User::whereRoleIs("admin")->get();
        return AdminUserResource::collection($allAdmin);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|max:30|string',
            'surname' => 'required|max:30|string',
            'phone_number' => 'required|string|unique:users',
            'firebase_token' => 'required|string',
            'password' => 'required|string|min:6|max:18',
            'picture' => 'sometimes|image|mimes:jpeg,png,jpg|max:8192',
            'dob' => 'required|date_format:Y-m-d',
            'village' => 'required|string|max:50',
            'city' => 'required|string|max:50',
            'province' => 'required|string|max:50',
        ]);
        if (Auth::user()->hasRole("superadmin")) {

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

            if ($user->save()) {
                $user->attachRole("admin");
                $http = new \GuzzleHttp\Client([
                    'timeout' => 60,
                ]);
                try {
                    $response = $http->post(\Config::get("values.APP_URL") . ':' . $_SERVER["SERVER_PORT"] . '/oauth/token', [
                        'form_params' => [
                            'grant_type' => 'password',
                            'client_id' => \Config::get("values.CLIENT_ID"),
                            'client_secret' => \Config::get("values.CLIENT_SECRET"),
                            'username' => $request->phone_number,
                            'password' => $request->password,
                        ],
                    ]);

                    /*  $database = app('firebase.database');
                    $reference = $database->getReference('users/' . $uid . '/')
                    ->push([
                    'profile' => $defaultImage, // new highest price
                    ]);*/

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
        } else {
            return response()->json(["message" => "only super admin can add admin"], 401);
        }
    }

    public function login(Request $request)
    {

        $request->validate([
            'phone_number' => 'required',
            'password' => 'required|string|min:6|max:18',
        ]);

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

    }

}
