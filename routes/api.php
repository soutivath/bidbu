<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Kreait\Firebase\DynamicLink\AnalyticsInfo;
use Kreait\Firebase\DynamicLink\AnalyticsInfo\GooglePlayAnalytics;
use Kreait\Firebase\DynamicLink\AnalyticsInfo\ITunesConnectAnalytics;
use Kreait\Firebase\DynamicLink\AndroidInfo;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */


Route::post('/register', [App\Http\Controllers\apiAuthController::class, 'register']);
Route::post('/bidding', [App\Http\Controllers\BuddhistController::class, 'bidding']);
Route::post('/store', [App\Http\Controllers\BuddhistController::class, 'store']);
Route::post('/login', [App\Http\Controllers\apiAuthController::class, 'login']);
Route::post("/logout", [App\Http\Controllers\apiAuthController::class, 'logOut']);
Route::post("/admin/logout", [App\Http\Controllers\AdminBuddhistController::class, 'logOut']);

Route::get('type', [App\Http\Controllers\TypeController::class, "index"]);
Route::post('type', [App\Http\Controllers\TypeController::class, "store"]);
Route::get('type/{id}', [App\Http\Controllers\TypeController::class, "show"]);
Route::put('type', [App\Http\Controllers\TypeController::class, "update"]);
Route::delete('type/{id}', [App\Http\Controllers\TypeController::class, "destroy"]);

Route::get('buddhist', [App\Http\Controllers\BuddhistController::class, "index"]);
Route::post('buddhist', [App\Http\Controllers\BuddhistController::class, "store"]);
Route::delete('buddhist', [App\Http\Controllers\BuddhistController::class, "destroy"]);
//post comment
Route::post('/buddhist/comment/', [App\Http\Controllers\CommentController::class, 'store']);
//update Comment
Route::put('buddhist/comment/', [App\Http\Controllers\CommentController::class, 'update']);
//delete comment
Route::delete('buddhist/comment/', [App\Http\Controllers\CommentController::class, 'destroy']);

//Reply

//post reply
Route::prefix('buddhist/comment/')->group(function () {
    //Route::get('reply', [App\Http\Controllers\ReplyController::class, 'index']);

    //update reply
    Route::put('reply/', [App\Http\Controllers\ReplyController::class, 'update']);

    //store reply
    Route::post('reply', [App\Http\Controllers\ReplyController::class, 'store']);

    //delete
    Route::delete('reply/', [App\Http\Controllers\ReplyController::class, 'destroy']);
});

//get buddhist by type id
Route::get('typeBuddhist/{type_id}', [App\Http\Controllers\BuddhistController::class, 'buddhistType']);

//getFavorite
Route::get(('favorite/buddhist/'), [App\Http\Controllers\FavouriteController::class, 'index']);
//add or delete into favorite
Route::post('favorite/buddhist/', [App\Http\Controllers\FavouriteController::class, 'store']);

//user
Route::get('user', [App\Http\Controllers\ProfileController::class, 'show']);

Route::get("profileReview/{id}", [App\Http\Controllers\ProfileController::class, "getReviewByUserId"]);

//Route::get('nice',[App\Http\Controllers\BuddhistController::class,'testTokenGetData']);
Route::get("user/{id}", [App\Http\Controllers\ProfileController::class, 'getUserByID']);

//custom
/*$middleware =['api'];
if (\Request::header('Authorization'))
$middleware = array_merge(['auth:api']);
Route::group(['namespace' => 'Api', 'middleware' => $middleware], function () {
//routes here
Route::get('buddhist/{id}', [App\Http\Controllers\BuddhistController::class,"show"]);
});*/

Route::get('buddhist/{id}', [App\Http\Controllers\BuddhistController::class, "show"]);
Route::get("recommended/{type_id}/{buddhist_id}", [App\Http\Controllers\BuddhistController::class, "recommendedBuddhist"]);
Route::post("reset", [App\Http\Controllers\apiAuthController::class, "forgetPassword"]);

//admin

Route::get("/admin/buddhist", [App\Http\Controllers\AdminBuddhistController::class, "index"]);
Route::get("/admin/active-buddhist", [App\Http\Controllers\AdminBuddhistController::class, "getActive"]);

Route::get("/admin/non-active-buddhist", [App\Http\Controllers\AdminBuddhistController::class, "getNonActive"]);

Route::put("/admin/user/disable/", [App\Http\Controllers\AdminBuddhistController::class, "disableUser"]);
Route::put("/admin/user/disable/admin", [App\Http\Controllers\AdminBuddhistController::class, "disableAdmin"]);

//all admin
Route::get("/admin/allAdmin", [App\Http\Controllers\AdminBuddhistController::class, "getAdminRole"]);
Route::get("/admin/allActiveAdmin", [App\Http\Controllers\AdminBuddhistController::class, "getActiveAdminRole"]);
Route::get("/admin/allNonActiveAdmin", [App\Http\Controllers\AdminBuddhistController::class, "getNonActiveAdminRole"]);

Route::post("/admin/register", [App\Http\Controllers\AdminBuddhistController::class, "register"]);
Route::get("/admin/user/{id}", [App\Http\Controllers\AdminBuddhistController::class, "getUserByID"]);
Route::get("/admin/buddhist/{buddhist_id}", [App\Http\Controllers\AdminBuddhistController::class, "getBuddhistByID"]);
Route::put("/admin/buddhist/disable/", [App\Http\Controllers\AdminBuddhistController::class, "disableBuddhist"]);
Route::get("/admin/buddhist/disabled/", [App\Http\Controllers\AdminBuddhistController::class, "getDisableBuddhist"]);
Route::post("/admin/login", [App\Http\Controllers\AdminBuddhistController::class, "login"]);

Route::get("/biddingLose", [App\Http\Controllers\BuddhistController::class, "biddingLose"]);
Route::get("/biddingWin", [App\Http\Controllers\BuddhistController::class, "biddingWin"]);

Route::get("/myActiveBuddhist", [App\Http\Controllers\BuddhistController::class, "myActiveBuddhist"]);

Route::get("/mySoldOutBuddhist", [App\Http\Controllers\BuddhistController::class, "mySoldOutBuddhist"]);
Route::get("/myNonSoldOutBuddhist", [App\Http\Controllers\BuddhistController::class, "myNonSoldOutBuddhist"]);

Route::get("admin/getAllUser", [App\Http\Controllers\AdminBuddhistController::class, "getAllUser"]);
Route::get("admin/getActiveUser", [App\Http\Controllers\AdminBuddhistController::class, "getActiveUser"]);
Route::get("admin/getDisabledUser", [App\Http\Controllers\AdminBuddhistController::class, "getDisabledUser"]);

Route::get("/biddingNotification", [App\Http\Controllers\notificationController::class, "biddingNotification"]);
Route::get("/messageNotification", [App\Http\Controllers\notificationController::class, "messageNotification"]);
Route::get("/biddingResultNotification", [App\Http\Controllers\notificationController::class, "biddingResultNotification"]);

Route::get("unreadBiddingCount", [App\Http\Controllers\notificationController::class, 'unreadBiddingCount']);
Route::get("unreadMessageCount", [App\Http\Controllers\notificationController::class, 'unreadMessageCount']);
Route::get("unReadBiddingResult", [App\Http\Controllers\notificationController::class, 'unReadBiddingResult']);

Route::get("checkToken", [App\Http\Controllers\apiAuthController::class, "checkToken"]);

Route::get("checkBuddhistResult/{id}", [App\Http\Controllers\BuddhistController::class, "checkBuddhistResult"]);
Route::get("participantBidding", [App\Http\Controllers\BuddhistController::class, "participantBidding"]);

Route::get("testNotification/", [App\Http\Controllers\testController::class, "testNotification"]);
Route::get("most_like/", [App\Http\Controllers\BuddhistController::class, "countByFavorite"]);
Route::get("nearly_end/", [App\Http\Controllers\BuddhistController::class, "almostEnd"]);
Route::put("editProfile/", [App\Http\Controllers\ProfileController::class, "editProfile"]);
Route::get("recommended/", [App\Http\Controllers\RecommendedBuddhistController::class, "index"]);
Route::post("recommended/", [App\Http\Controllers\RecommendedBuddhistController::class, "store"]);
Route::get("recommended/allBuddhist", [App\Http\Controllers\RecommendedBuddhistController::class, "allBuddhist"]);
Route::delete("/notification", [App\Http\Controllers\notificationController::class, "deleteNotification"]);

Route::post("check_chat_room", [App\Http\Controllers\InboxChatController::class, "createChatRoom"]);
Route::post("chat", [App\Http\Controllers\InboxChatController::class, "sendMessage"]);

Route::prefix('/language')->group(function () {
    Route::get("/", [App\Http\Controllers\LanguageController::class, "getAll"]);
    Route::post("/", [App\Http\Controllers\LanguageController::class, "post"]);
    Route::put("/{language_id}", [App\Http\Controllers\LanguageController::class, "update"]);
    Route::get("/{language_id}", [App\Http\Controllers\LanguageController::class, "get"]);
    Route::delete("/{language_id}", [App\Http\Controllers\LanguageController::class, "destroy"]);
});

Route::prefix("/review")->group(function () {
    Route::post("/", [App\Http\Controllers\ReviewController::class, "store"]);
    Route::put("/", [App\Http\Controllers\ReviewController::class, "update"]);
    Route::delete("/{user_id}", [App\Http\Controllers\ReviewController::class, "destroy"]);
    Route::get("/{user_id}", [App\Http\Controllers\ReviewController::class, "getReview"]);
});

Route::prefix('/banner')->group(function () {
    Route::post("/", [App\Http\Controllers\ShowBannerController::class, "post"]);
    Route::put("/{banner_id}", [App\Http\Controllers\ShowBannerController::class, "update"]);
    Route::delete("/{banner_id}", [App\Http\Controllers\ShowBannerController::class, "destroy"]);
    Route::get("/getAll", [App\Http\Controllers\ShowBannerController::class, "getAll"]);
    Route::get("/{banner_id}", [App\Http\Controllers\ShowBannerController::class, "show"]);
});
Route::post("/quickActiveBanner", [App\Http\Controllers\ShowBannerController::class, "quickActiveBanner"]);
Route::get("/viewActiveBanner", [App\Http\Controllers\ShowBannerController::class, "viewActiveBanner"]);
Route::get("/viewNonActiveBanner", [App\Http\Controllers\ShowBannerController::class, "viewNonActiveBanner"]);

//Route::get("/paymentDetail",[App\Http\Controllers\Payment::class,"buyCoins"]);
if (config('app.debug') == true) {
    Route::get("nice", [App\Http\Controllers\GetToken::class, "getToken"]);
    Route::get("nice/admin", [App\Http\Controllers\GetToken::class, "getTokenUser"]);
}


Route::post("/sendNotification", [App\Http\Controllers\SendNotification::class, "sendAll"]);


Route::prefix("/verify")->group(function () {
    // Route::post("/", [App\Http\Controllers\VerifyController::class, "requestVerify"]);
    // Route::get("/", [App\Http\Controllers\VerifyController::class, "getAllVerify"]);
    // Route::get("/{id}", [App\Http\Controllers\VerifyController::class, "viewVerify"]);
    // Route::post("/{id}", [App\Http\Controllers\VerifyController::class, "operateVerification"]);

    Route::get("/", [App\Http\Controllers\VerifyController::class, "getAllVerification"]);
    Route::post("/", [App\Http\Controllers\VerifyController::class, "fileVerifyRequest"]);
    Route::put("/{id}", [App\Http\Controllers\VerifyController::class, "updateVerify"]);
    Route::get("/{id}", [App\Http\Controllers\VerifyController::class, "viewVerify"]);
    
});

Route::get("/verify_admin/{id}", [App\Http\Controllers\VerifyController::class, "adminViewVerify"]);
Route::post("/verify_number", [App\Http\Controllers\VerifyController::class, "verifyNumber"]);
Route::post("/verify_personal", [App\Http\Controllers\VerifyController::class, "verifyPersonalData"]);
Route::put("/verify_emergency_phone_number",[App\Http\Controllers\VerifyController::class, "addEmergencyPhone"]);


Route::post("facebook_signin", [App\Http\Controllers\apiAuthController::class, "facebook_one_click_login_register"]);
Route::get("item_by_user/{id}",[App\Http\Controllers\ProfileController::class,"itemBelongToUser"]);
Route::get("participant_by_user/{id}",[App\Http\Controllers\ProfileController::class,"userItemParticipant"]);

Route::post("remove_lasted_bid/{id}",[App\Http\Controllers\BiddingController::class,"removeLastedBidItem"]);

Route::get("load_verify_image/{folder}/{image_name}",[App\Http\Controllers\PrivateController::class,"getImageFileVerify"])->where(['image_name'=>'^([A-z0-9-_+]+\/)*([A-z0-9-\.]+)(\?\d+)?$']);
Route::post("get_custom_token",[App\Http\Controllers\apiAuthController::class,"getCustomToken"]);

Route::get("kong_dee_center",[App\Http\Controllers\ShowItemSectionController::class,"kongDeeCenter"]);

Route::get("mkdir",function(){
    $folderName = uniqid() . "_" . time();
            
    $base_verify_location = base_path("nice");
  
    $base_verify_file_location = base_path("resources/private/verify/". $folderName);
    if (!File::isDirectory($base_verify_location)) {
        File::makeDirectory($base_verify_location,0775, true,true);
    }
 
    return response()->json(["data"=>"successfully"]);
});

