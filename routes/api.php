<?php

use App\Http\Controllers\apiAuthController;
use Illuminate\Http\Request;
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
Route::post('/bidding/{id}', [App\Http\Controllers\BuddhistController::class, 'bidding']);
Route::post('/store', [App\Http\Controllers\BuddhistController::class, 'store']);
Route::post('/login', [App\Http\Controllers\apiAuthController::class, 'login']);


Route::apiResource('type', App\Http\Controllers\TypeController::class);

Route::get('buddhist', [App\Http\Controllers\BuddhistController::class,"index"]);
Route::post('buddhist', [App\Http\Controllers\BuddhistController::class,"store"]);
Route::delete('buddhist/{id}', [App\Http\Controllers\BuddhistController::class,"destroy"]);
//post comment
Route::post('/buddhist/{buddhist_id}/comment', [App\Http\Controllers\CommentController::class, 'store']);
//update Comment
Route::put('buddhist/{buddhist_id}/comment/{comment_id}', [App\Http\Controllers\CommentController::class, 'update']);
//delete comment
Route::delete('buddhist/{buddhist_id}/comment/{comment_id}', [App\Http\Controllers\CommentController::class, 'destroy']);


//Reply

//post reply
Route::prefix('buddhist/{buddhist_id}/comment/{comment_id}')->group(function () {
    //Route::get('reply', [App\Http\Controllers\ReplyController::class, 'index']);

    //update reply
    Route::put('reply/{reply_id}', [App\Http\Controllers\ReplyController::class, 'update']);

    //store reply
    Route::post('reply', [App\Http\Controllers\ReplyController::class, 'store']);

    //delete
    Route::delete('reply/{reply_id}', [App\Http\Controllers\ReplyController::class, 'destroy']);
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
Route::get("user/{id}",[App\Http\Controllers\ProfileController::class, 'getUserByID']);

Route::get("notification",[App\Http\Controllers\notificationController::class,'index']);

//custom
/*$middleware =['api'];
if (\Request::header('Authorization')) 
   $middleware = array_merge(['auth:api']);
Route::group(['namespace' => 'Api', 'middleware' => $middleware], function () {
    //routes here
    Route::get('buddhist/{id}', [App\Http\Controllers\BuddhistController::class,"show"]);
});*/


Route::get('buddhist/{id}', [App\Http\Controllers\BuddhistController::class,"show"]);
Route::get("recommended/{type_id}/{buddhist_id}",[App\Http\Controllers\BuddhistController::class,"recommendedBuddhist"]);