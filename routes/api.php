<?php

use App\Http\Controllers\Answer\AnswerController;
use App\Http\Controllers\auth\CoachController;
use App\Http\Controllers\auth\ForgotPasswordController;
use App\Http\Controllers\auth\PlayerController;
use App\Http\Controllers\Chat\CommunityMessageController;
use App\Http\Controllers\Chat\WebSocketController;
use App\Http\Controllers\plans\DoneplaneController;
use App\Http\Controllers\player\ProfileController;
use App\Http\Controllers\plans\PlandatesController;
use App\Http\Controllers\player\FreeCommunityController;
use App\Http\Controllers\player\HomeController;
use App\Http\Controllers\coach\CoachComController;
use App\Http\Controllers\player\PreCommunityController;
use App\Http\Controllers\AddplanController;
use App\Http\Controllers\coach\BooksessionController;
use App\Http\Controllers\coach\CommunityDetailsController;
use App\Http\Controllers\coach\EditcomController;
use App\Http\Controllers\coach\HomeCommunityController;
use App\Http\Controllers\FileController;
use App\Models\File;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Symfony\Component\HttpKernel\Profiler\Profile;

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

    Route::post('recommended', [HomeController::class, 'recommended']);


    Route::get('/join', [FreeCommunityController::class, 'join']);
    Route::get('/community', [FreeCommunityController::class, 'community']);
    Route::get('/leaderboard', [FreeCommunityController::class, 'leaderboard']);
    Route::get('/com_pre/leaderboard', [PreCommunityController::class, 'leaderboard']);

    Route::get('/plan', [FreeCommunityController::class, 'plan']);

    Route::post('/pre-join', [PreCommunityController::class, 'join']);
    Route::get('/availableDates', [PreCommunityController::class, 'availableDates']);

    Route::get('/userInfo', [ProfileController::class, 'getUserInfo']);
    Route::get('/score', [ProfileController::class, 'score']);
    Route::post('/answers', [AnswerController::class, 'storeAnswers']);
    Route::get('/getanswers', [AnswerController::class, 'getUserAnswer']);
    Route::get('/get_recommendation_answers', [AnswerController::class, 'getrecommendationanswers']);


    Route::post('/done', [DoneplaneController::class, 'updateProgress']); // تحديث التقدم
    Route::get('/get-sessions', [DoneplaneController::class, 'getsessions']);
    Route::get('/get-session-content', [DoneplaneController::class, 'getsession_content']);
    Route::get('/get-tasks', [DoneplaneController::class, 'gettasks']);
    Route::get('/get-progress', [DoneplaneController::class, 'getProgress']);


    Route::post('/community/message/send', [WebSocketController::class, 'sendMessage']);
    Route::get('/community/messages', [WebSocketController::class, 'index']);

    Route::post('/booksession', [DoneplaneController::class, 'booksession']);

});

Route::middleware(['jwt.auth'])->post('/updateimage', [ProfileController::class, 'updateimage']);
Route::middleware(['jwt.auth'])->post('/editprofile', [ProfileController::class, 'editprofile']);
Route::middleware(['jwt.auth'])->post('/logoutcom', [ProfileController::class, 'logoutcom']);
Route::middleware(['jwt.auth'])->post('/updatepassword', [ProfileController::class, 'updatepassword']);

Route::middleware(['jwt.auth'])->post('/store-score', [PlandatesController::class, 'storeScore']);
Route::middleware(['jwt.auth'])->get('/get-scores-last-7-days', [PlandatesController::class, 'getScoresForLast7Days']);


//////////////////////////////////////////////////////    COACH    ////////////////////////////////////////////////////////////////
Route::middleware(['jwt.auth'])->prefix('coach')->group(function () {
Route::post('/compre/create', [CoachComController::class, 'createCompre']);
Route::get('/plans', [CoachComController::class, 'getPlansByLevel']);
Route::get('/compre/my-compres', [ProfileController::class, 'getUserCompres']);
Route::post('/card/store', [CoachComController::class, 'storeCard']);
Route::post('/sessions', [CoachComController::class, 'getSessionsByPlanId']);
Route::get('/communtities', [HomeCommunityController::class, 'getCoachCommunities']);
Route::get('/players', [HomeCommunityController::class, 'getCoachPlayersStatus']);
Route::get('/players-count', [HomeCommunityController::class, 'getPremiumCommunityPlayersCount']);
Route::get('/community-details', [CommunityDetailsController::class, 'getCommunityDetails']);
Route::get('/community-members-status', [CommunityDetailsController::class, 'getCommunityPlayersStatus']);
Route::post('/delete-community', [EditcomController::class, 'deleteCommunity']);
Route::post('/update-community-name', [EditcomController::class, 'updateCommunityName']);
Route::post('/remove-player-from-community', [EditcomController::class, 'removePlayerFromCommunity']);
Route::get('/leaderboard', [CoachComController::class, 'leaderboard']);
Route::get('/booked-sessions', [BooksessionController::class, 'booksession']);

});


Route::middleware(['jwt.auth'])->prefix('file')->group(function () {
    Route::post('/upload/rec3', [FileController::class, 'getRec3']);
    Route::post('/get-recording', [FileController::class, 'getRecording']);
    });


Route::post('/plans/upload', [AddplanController::class, 'storePlan']);
Route::post('/sessions/upload', [AddplanController::class, 'storeSession']);
Route::post('/sessions/update', [AddplanController::class, 'updateSession']);

Route::post('/upload', [FileController::class, 'upload']);

Route::get('/plans', [AddplanController::class, 'plans']);
