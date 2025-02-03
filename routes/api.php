<?php

use App\Http\Controllers\auth\CoachController;
use App\Http\Controllers\auth\ForgotPasswordController;
use App\Http\Controllers\auth\PlayerController;
use App\Http\Controllers\player\ProfileController;
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
Route::post('/player/logout', [PlayerController::class, 'logout'])->middleware('jwt.auth');
///////coach////////
Route::post('/coach/sign', [CoachController::class, 'sign']);
Route::post('/coach/login', [CoachController::class, 'login']);
Route::post('/coach/logout', [CoachController::class, 'logout'])->middleware('jwt.auth');
///////forgetpassword////////
Route::post('sendCode', [ForgotPasswordController::class, 'sendCode']);
Route::post('vertifyCode', [ForgotPasswordController::class, 'vertifyCode']);
Route::post('resetPassword', [ForgotPasswordController::class, 'resetPassword']);
<<<<<<< HEAD
Route::post('send-verification-code', [ForgotPasswordController::class, 'sendemailcode']);
=======
>>>>>>> 72322d1e17272618aae7f9bb2401cd1fb28d9351
///////////////////////////////////////////////////////////////////////////////////////////////////////////


//////////////////////////////////////    PLAYER    ////////////////////////////////////////////////////////////////
Route::middleware(['jwt.auth'])->prefix('player')->group(function () {
    Route::get('/userInfo', [ProfileController::class, 'getUserInfo']);
    Route::post('/updateScore', [ProfileController::class, 'updateScore']);
    Route::get('/score', [ProfileController::class, 'score']);

});