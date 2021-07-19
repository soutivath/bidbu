<?php

use App\Http\Controllers\apiAuthController;
use Illuminate\Support\Facades\Route;

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
Route::post("/logout", [App\Http\Ccontrollers\apiAuthController::class, 'logOut']);
Route::apiResource('type', App\Http\Controllers\TypeController::class);

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
Route::get("/admin/user", [App\Http\Controllers\AdminBuddhistController::class, "getAllUser"]);

Route::post("/admin/user/disable/", [App\Http\Controllers\AdminBuddhistController::class, "disableUser"]);

Route::get("/admin/allAdmin", [App\Http\Controllers\AdminBuddhistController::class, "getAdminRole"]);
Route::post("/admin/register", [App\Http\Controllers\AdminBuddhistController::class, "register"]);
Route::get("/admin/user/{id}", [App\Http\Controllers\AdminBuddhistController::class, "getUserByID"]);
Route::delete("/admin/buddhist/disable/", [App\Http\Controllers\AdminBuddhistController::class, "disableBuddhist"]);
Route::get("/admin/buddhist/disabled/", [App\Http\Controllers\AdminBuddhistController::class, "getDisableBuddhist"]);
Route::post("/admin/login", [App\Http\Controllers\AdminBuddhistController::class, "login"]);

Route::get("/biddingLose", [App\Http\Controllers\BuddhistController::class, "biddingLose"]);
Route::get("/biddingWin", [App\Http\Controllers\BuddhistController::class, "biddingWin"]);
Route::get("/myActiveBuddhist", [App\Http\Controllers\BuddhistController::class, "myActiveBuddhist"]);
Route::get("/myDisabledBuddhist", [App\Http\Controllers\BuddhistController::class, "myDisabledBuddhist"]);
Route::get("/myNonDisabledBuddhist", [App\Http\Controllers\BuddhistController::class, "myNonDisabledBuddhist"]);

Route::get("/biddingNotification", [App\Http\Controllers\notificationController::class, "biddingNotification"]);
Route::get("/messageNotification", [App\Http\Controllers\notificationController::class, "messageNotification"]);
Route::get("/biddingResultNotification", [App\Http\Controllers\notificationController::class, "biddingResultNotification"]);

Route::get("unreadBiddingCount", [App\Http\Controllers\notificationController::class, 'unreadBiddingCount']);
Route::get("unreadMessageCount", [App\Http\Controllers\notificationController::class, 'unreadMessageCount']);
Route::get("unReadBiddingResult", [App\Http\Controllers\notificationController::class, 'unReadBiddingResult']);

Route::get("checkToken", [App\Http\Controllers\apiAuthController::class, "checkToken"]);
