<?php

namespace App\Http\Controllers;

use App\Http\Resources\Admin\AdminBuddhistResource;
use App\Http\Resources\Admin\AdminUserResource;
use App\Models\Buddhist;
use App\Models\User;
use Auth;
use carbon\Carbon;
use File;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Image;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class AdminBuddhistController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:api")->except("login");
        $this->middleware("checkAdminIsActive:api")->except("login");
    }

    public function index()
    {

        $buddhist = Buddhist::orderBy('created_at', 'desc')->with(["type", "user"])->get();

        return AdminBuddhistResource::collection($buddhist);
    }

    public function getActive()
    {
        $buddhist = Buddhist::where('end_time', '>', Carbon::now())->with('type')->orderBy("created_at", "desc")->get();
        return AdminBuddhistResource::collection($buddhist);
    }

    public function getNonActive()
    {
        $buddhist = Buddhist::where('end_time', '<=', Carbon::now())->where('active', "!=", "disabled")->with('type')->orderBy("created_at", "desc")->get();

        return AdminBuddhistResource::collection($buddhist);

    }

    public function disableBuddhist(Request $request)
    {

        $request->validate([
            "id" => "required|integer",
        ]);

        $buddhist = Buddhist::find($request->id);
        if ($buddhist != null && $buddhist->active == 1) {
            $buddhist->active = "disabled";
            $buddhist->end_time = now();
            $buddhist->save();

            $messaging = app('firebase.messaging');

            $notification = Notification::fromArray([
                'title' => 'ແຈ້ງເຕຶອນໃໝ່ຈາກ ' . $buddhist->name,
                'body' => "ລາຍການຂອງທ່ານໄດ້ຖຶກລົບຈາກລະບົບ",
                'image' => \public_path("/notification_images/chat.png"),
            ]);
            $notification_data = [
                'sender' => Auth::id(),
                'buddhist_id' => $buddhist->id,
                'page' => 'content_detail',
            ];
            $message = CloudMessage::withTarget('topic', $buddhist->user->topic)
                ->withNotification($notification)
                ->withData($notification_data);
            $messaging->send($message);

            return response()->json(["message" => "Disable buddhist successfully"], 200);
        } else {
            return response()->json(["message" => "No data or this item already close"], 404);
        }
    }

    public function getDisableBuddhist()
    {
        $buddhist = Buddhist::where("active", "disabled")->with('type')->orderBy("created_at", "desc")->get();
        return AdminBuddhistResource::collection($buddhist);
    }

    public function getAllUser()
    {
        $user = User::whereRoleIs(["bond", "premium", "gold"])->get();
        return AdminUserResource::collection($user);
    }
    public function getActiveUser()
    {
        $user = User::whereRoleIs(["bond", "premium", "gold"])->where("active", "1")->get();
        return AdminUserResource::collection($user);
    }
    public function getDisabledUser()
    {
        $user = User::whereRoleIs(["bond", "premium", "gold"])->where("active", "0")->get();
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
    public function getBuddhistByID($buddhist_id)
    {
        $buddhist = Buddhist::where("id", $buddhist_id)->with("user")->first();
        if ($buddhist != null) {
            return new AdminBuddhistResource($buddhist);
        } else {
            return response()->json(["message" => "Item not found"], 404);
        }

    }

    public function disableUser(Request $request)
    {

        $request->validate([
            "id" => "required",
        ]);
        if (Auth::user()->hasRole(["superadmin", "admin"])) {
            $request->validate([
                "id" => "required|integer",
            ]);
            if (Auth::id() === $request->id) {
                return response()->json(["message" => "ບໍ່ສາມາດປິດບັນຊີທີ່ໃຊ້ງານຢູ່ໄດ້"], 405);
            }
            try {
                $user = User::findOrFail($request->id);
                if ($user->hasRole("superadmin")) {
                    return response()->json(["message" => "you can't change super admin status"], 200);
                }
                if ($user->active == "0") {
                    $user->active = "1";
                    $user->save();
                    return response()->json([
                        "message" => "ເປິດໃຊ້ງານບັນຊີຂອງ" . $user->name . "ສຳເລັດແລ້ວ",
                    ], 200);

                } else {

                    $user->active = "0";

                    $user->save();
                    return response()->json([
                        "message" => "ປິດໃຊ້ງານບັນຊີຂອງ" . $user->name . "ສຳເລັດແລ້ວ",
                    ], 200);
                }

            } catch (ModelNotFoundException $e) {
                return response()->json(["message" => "User not found"], 404);
            }

        } else {
            return response()->json(["message" => "you don't have permission to access this content"], 403);
        }

    }
    public function disableAdmin(Request $request)
    {
        $request->validate([
            "id" => "required",
        ]);
        if (Auth::user()->hasRole("superadmin")) {
            $request->validate([
                "id" => "required|integer",
            ]);
            if (Auth::id() === $request->id) {
                return response()->json(["message" => "ບໍ່ສາມາດປິດບັນຊີທີ່ໃຊ້ງານຢູ່ໄດ້"], 405);
            }
            try {
                $user = User::findOrFail($request->id);
                if ($user->hasRole("superadmin")) {
                    return response()->json(["message" => "you can't change super admin status"], 200);
                }
                if ($user->active == "0") {
                    $user->active = "1";
                    $user->save();
                    return response()->json([
                        "message" => "ເປິດໃຊ້ງານບັນຊີຂອງ" . $user->name . "ສຳເລັດແລ້ວ",
                    ], 200);

                } else {

                    $user->active = "0";

                    $user->save();
                    return response()->json([
                        "message" => "ປິດໃຊ້ງານບັນຊີຂອງ" . $user->name . "ສຳເລັດແລ້ວ",
                    ], 200);
                }

            } catch (ModelNotFoundException $e) {
                return response()->json(["message" => "User not found"], 404);
            }

        } else {
            return response()->json(["message" => "you don't have permission to access this content"], 403);
        }

    }

    public function getAdminRole()
    {
        $allAdmin = User::whereRoleIs(["admin", "superadmin"])->get();
        return AdminUserResource::collection($allAdmin);
    }
    public function getActiveAdminRole()
    {
        $allAdmin = User::whereRoleIs(["admin", "superadmin"])->where("active", "1")->get();
        return AdminUserResource::collection($allAdmin);
    }
    public function getNonActiveAdminRole()
    {
        $allAdmin = User::whereRoleIs(["admin", "superadmin"])->where("active", "0")->get();
        return AdminUserResource::collection($allAdmin);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|max:30|string',
            'surname' => 'required|max:30|string',
            'phone_number' => 'required|string|unique:users',
            //  'firebase_token' => 'required|string',
            'password' => 'required|string|min:6|max:18',
            'picture' => 'sometimes|image|mimes:jpeg,png,jpg|max:30720',

        ]);

        if (Auth::user()->hasRole("superadmin")) {

            #  $auth = app('firebase.auth');
            #  $idTokenString = $request->firebase_token;
            #  try { // Try to verify the Firebase credential token with Google
            #      $verifiedIdToken = $auth->verifyIdToken($idTokenString);
            #  } catch (\InvalidArgumentException $e) { // If the token has the wrong format
            ##      return response()->json(
            ##         [
            ##            'message' => 'Unauthorized - Can\'t parse the token: ' . $e->getMessage(),
            #        ], 401
            #    );
            # } catch (InvalidToken $e) { // If the token is invalid (expired ...)

            #     return response()->json([
            ##         'message' => 'Unauthorized - Token is invalid: ' . $e->getMessage(),
            #    ], 401);
            # }

            // Retrieve the UID (User ID) from the verified Firebase credential's token
            #   $uid = $verifiedIdToken->claims()->get('sub');
            try {
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
                $user->firebase_uid = "admin" . time();
                $user->password = bcrypt($request->password);
                $user->picture = $defaultImage;

                $user->topic = "notification_topic_" . "admin" . time();
                $user->save();
                $user->attachRole("admin");

                /* if ($user->save()) {
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
                 */
                /*  $database = app('firebase.database');
                $reference = $database->getReference('users/' . $uid . '/')
                ->push([
                'profile' => $defaultImage, // new highest price
                ]);*/

                // new AdminLoginResponseResource($response->getBody());
                //return $response->getBody();
                /* return response()->json([
                "role" => Auth::user()->roles[0]->name,
                "data" => $response->getBody(),

                ], 200);*/
                return response()->json([

                    "message" => "register admin successfully",

                ], 201);

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
            return response()->json(['message' => 'ເບີໂທຫຼືລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ'], 401);
        }
        if (Auth::user()->active == 0) {
            return response()->json(['message' => 'Your account has been dismiss'], 403);

        }
        if (Auth::user()->hasRole(["admin", "superadmin"]) == false) {
            return response()->json(["message" => "Only admin can access to this content"], 401);
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

            return response()->json([
                "role" => Auth::user()->roles[0]->name,
                "name" => Auth::user()->name,
                "data" => json_decode($response->getBody()->getContents()),

            ], 200);

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

    public function updateProfile(Request $request)
    {
        $request->validate([
            "name" => "required",
            "surname" => "required",
            "village" => "required",
            "city" => "required",
            "province" => "required",
            'picture' => 'sometimes|image|mimes:jpeg,png,jpg|max:8192',
        ]);
        $user = User::find(Auth::id());
        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->village = $request->village;
        $user->city = $request->city;
        $user->province = $request->province;

        if ($request->hasFile('picture')) {
            $image = $request->file('picture');
            $fileExtension = $image->getClientOriginalExtension();
            $fileName = 'profile_image_' . time() . '.' . $fileExtension;
            $location = public_path("/profile_image/" . $fileName);
            Image::make($image)->resize(460, null, function ($c) {$c->aspectRatio();})->save($location);
            $path = public_path() . '/profile_image/' . $user->picture;
            if (\file_exists($path)) {
                unlink(public_path() . '/profile_image/' . $user->picture);
            }
            $user->picture = $fileName;
        }
        $user->save();
        return response()->json(["message" => "update your information successfully"], 200);
    }

    public function logOut()
    {

        auth()->user()->tokens->each(function ($token, $key) {
            $token->delete();
        });
        return response()->json('Logout Successfully', 200);
    }

}
