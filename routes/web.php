<?php

use App\Http\Controllers\dashboard\CommunityController;
use App\Http\Controllers\dashboard\PlayersController;
use App\Models\Plan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\plans\PlandatesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [PlayersController::class, 'index'])->name('dashboard');

Route::get('/community', [CommunityController::class, 'index'])->name('communities');

Route::post('/community', [CommunityController::class, 'store'])->name('communities.store');
