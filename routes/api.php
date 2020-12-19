<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\apiAuthController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register',[App\Http\Controllers\apiAuthController::class, 'register']);
Route::post('/bidding/{id}',[App\Http\Controllers\BuddhistController::class, 'bidding']);
Route::post('/store',[App\Http\Controllers\BuddhistController::class, 'store']);
Route::post('/login',[App\Http\Controllers\apiAuthController::class,'login']);

Route::post('altLogin',[App\Http\Controllers\apiAuthController::class,'altLogin']);
Route::apiResource('type',App\Http\Controllers\TypeController::class);
Route::apiResource('buddhist',App\Http\Controllers\BuddhistController::class);
//store buddhist
Route::post('/buddhist',[App\Http\Controllers\BuddhistController::class,'store']);

Route::get('getHigh',[App\Http\Controllers\BuddhistController::class,'getHigh']);
/*Route::get('/type',[App\Http\Controllers\TypeController::class,'index']);
Route::post('/type',[App\Http\Controllers\TypeController::class,'store']);
Route::get('/type/{id}',[App\Http\Controllers\TypeController::class,'show']);
Route::put('/type/{id}',[App\Http\Controllers\TypeController::class,'update']);
Route::delete('/type/{id}',[App\Http\Controllers\TypeController::class,'destroy']);*/

//comment
//Route::apiResource('comment',App\Http\Controllers\CommentController::class);

//post comment
Route::post('/buddhist/{buddhist_id}/comment',[App\Http\Controllers\CommentController::class,'store']);
//update Comment
Route::put('buddhist/{buddhist_id}/comment/{comment_id}',[App\Http\Controllers\CommentController::class,'update']);
//delete comment
Route::delete('buddhist/{buddhist_id}/comment/{comment_id}',[App\Http\Controllers\CommentController::class,'destroy']);
//get one comment
Route::get('buddhist/{buddhist_id}/comment/{comment_id}',[App\Http\Controllers\CommentController::class,'show']);

//Reply

//post reply
Route::prefix('buddhist/{buddhist_id}/comment/{comment_id}')->group(function () {
   Route::post('reply',[App\Http\Controllers\ReplyController::class,'store']);

   //get reply
   Route::get('reply/{reply_id}',[App\Http\Controllers\ReplyController::class,'show']);

   //update reply
   Route::put('reply/{reply_id}',[App\Http\Controllers\ReplyController::class,'update']);

   //store reply
   Route::post('reply',[App\Http\Controllers\ReplyController::class,'store']);

   //delete
   Route::delete('reply/{reply_id}',[App\Http\Controllers\ReplyController::class,'destroy']);
});

//get buddhist by id
Route::get('typeBuddhist/{type_id}',[App\Http\Controllers\BuddhistController::class,'buddhistType']);