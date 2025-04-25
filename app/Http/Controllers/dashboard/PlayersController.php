<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class PlayersController extends Controller
{
    public function index(){
        $users = User::with('comFree','comPre')->get();
        return view('dashboard',compact('users'));
    }
}
