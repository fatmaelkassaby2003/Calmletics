<?php

use App\Http\Controllers\Answer\AnswerController;
use App\Http\Controllers\auth\CoachController;
use App\Http\Controllers\auth\ForgotPasswordController;
use App\Http\Controllers\auth\PlayerController;
use App\Http\Controllers\plans\DoneplaneController;
use App\Http\Controllers\player\ProfileController;
use App\Http\Controllers\plans\PlandatesController;
use App\Http\Controllers\player\FreeCommunityController;
use App\Http\Controllers\player\HomeController;
use App\Http\Controllers\player\PreCommunityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//////////////////////////////////////      auth     ////////////////////////////////////////////////////////////////
///////player////////
Route::post('/player/sign', [PlayerController::class, 'sign']);
Route::post('/player/login', [PlayerController::class, 'login']);
Route::post('/player/logout', [ProfileController::class, 'logout'])->middleware('jwt.auth');
///////coach////////
Route::post('/coach/sign', [CoachController::class, 'sign']);
Route::post('/coach/login', [CoachController::class, 'login']);
Route::post('/coach/logout', [CoachController::class, 'logout'])->middleware('jwt.auth');
///////forgetpassword////////
Route::post('sendCode', [ForgotPasswordController::class, 'sendCode']);
Route::post('vertifyCode', [ForgotPasswordController::class, 'vertifyCode']);
Route::post('resetPassword', [ForgotPasswordController::class, 'resetPassword']);
Route::post('send-verification-code', [ForgotPasswordController::class, 'sendemailcode']);
///////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////    PLAYER    ////////////////////////////////////////////////////////////////
Route::middleware(['jwt.auth'])->prefix('player')->group(function () {
    
    Route::post('/getScore', [HomeController::class, 'getScore']);
    // Route::get('/userPlan', [HomeController::class, 'userPlan']);
    Route::post('/image', [HomeController::class, 'image']);
    Route::post('/flag', [HomeController::class, 'flag']);
    Route::post('cluster', [HomeController::class, 'Cluster']);


    Route::post('/join', [FreeCommunityController::class, 'join']);
    Route::post('/community', [FreeCommunityController::class, 'community']);
    Route::post('/leaderboard', [FreeCommunityController::class, 'leaderboard']);
    Route::get('/plan', [FreeCommunityController::class, 'plan']);

    Route::post('/pre-join', [PreCommunityController::class, 'join']);
    Route::get('/availableDates', [PreCommunityController::class, 'availableDates']);

    Route::get('/userInfo', [ProfileController::class, 'getUserInfo']);
    Route::get('/score', [ProfileController::class, 'score']);
    Route::post('/answers', [AnswerController::class, 'storeAnswers']);
    Route::get('/getanswers', [AnswerController::class, 'getUserAnswer']);

    Route::post('/done', [DoneplaneController::class, 'updateProgress']); // تحديث التقدم
    Route::get('/get-content', [DoneplaneController::class, 'getNextContent']);


});

Route::middleware(['jwt.auth'])->post('/updateimage', [ProfileController::class, 'updateimage']);
Route::middleware(['jwt.auth'])->post('/editprofile', [ProfileController::class, 'editprofile']);
Route::middleware(['jwt.auth'])->post('/logoutcom', [ProfileController::class, 'logoutcom']);
Route::middleware(['jwt.auth'])->post('/updatepassword', [ProfileController::class, 'updatepassword']);

Route::middleware(['jwt.auth'])->post('/store-score', [PlandatesController::class, 'storeScore']);
Route::middleware(['jwt.auth'])->get('/get-scores-last-7-days', [PlandatesController::class, 'getScoresForLast7Days']);
