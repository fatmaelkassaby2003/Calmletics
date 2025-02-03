<?php

namespace App\Http\Controllers\player;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ProfileController extends Controller
{
    
    public function getUserInfo(Request $request)
{
    $token = $request->bearerToken();

    if (!$token) {
        return response()->json(['error' => 'Unauthorized. Token not provided.'], 401);
    }

    try {
        $user = JWTAuth::authenticate($token);
    } catch (JWTException $e) {
        return response()->json(['error' => 'Invalid or expired token'], 401);
    }
    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'score' => $user->score,
        'role' => $user->role,
    ], 200);
}

public function updateScore(Request $request)
{
    $user = User::find(auth()->id());

    if (!$user) {
        return response()->json(['error' => 'Unauthorized. Token might be invalid or expired.'], 401);
    }

    $validator = Validator::make($request->all(), [
        'score' => 'required|integer|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user->score = $request->score;
    $user->save(); 

    return response()->json([
        'message' => 'Score updated successfully',
        'score' => $user->score,
    ], 200);
} 
    
        public function score(Request $request)
    {
        $topUsers = User::orderBy('score', 'desc')
<<<<<<< HEAD
                         ->select('name', 'score') 
                        //  ->pluck('name');
                         //->get(); 
    //                      $topUsers = User::orderBy('score', 'desc')
    //                  ->take(3)
                     ->get();
=======
                         //->select('name', 'score') 
                         ->pluck('name');
                         //->get(); 
    //                      $topUsers = User::orderBy('score', 'desc')
    //                  ->take(3)
    //                  ->get(['name', 'score']);
>>>>>>> 72322d1e17272618aae7f9bb2401cd1fb28d9351

    // return response()->json($topUsers);
        return response()->json($topUsers);
    }



}
