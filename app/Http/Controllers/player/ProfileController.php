<?php

namespace App\Http\Controllers\player;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use App\Models\ComPre;
use Illuminate\Support\Facades\Auth;


class ProfileController extends Controller
{
    
    public function getUserInfo(Request $request)
{
    $user = User::find(auth()->id());

    return response()->json([
        'id' => $user->id,
        'image' => $user->image,
        'name' => $user->name,
        'email' => $user->email,
        'score' => $user->score,
        'role' => $user->role,
    ], 200);
}    

public function updateimage(Request $request)
{
    $user = User::find(auth()->id());
    $user->image = $request->image;
    $user->save();
    return response()->json([
        'message' => 'Image updated successfully',
        'image' => $user->image,
    ], 200);


}

public function editprofile(Request $request)
{
    $user = User::find(auth()->id());
    if ($request->name) {
        $user->name = $request->name;
    }
    if ($request->email) {
        $user->email = $request->email;
    }
    $user->flag=$request->flag;
    $user->save();
    return response()->json([
        'message' => 'Profile updated successfully',
        'name' => $user->name,
        'email' => $user->email,
        'flag' => $user->flag,
    ], 200);
}

public function logout()
{
    try {
        JWTAuth::invalidate(JWTAuth::getToken()); 

        return response()->json(['message' => 'User successfully logged out']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Something went wrong'], 500);
    }
}

public function logoutcom(Request $request)
{
    try {
        $user = User::find(auth()->id());

        if ($user) {
            $user->com_free_id = null;
            $user->com_pre_id = null;
            $user->save(); 

            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json(['message' => 'User successfully logged out and fields reset']);
        }

        return response()->json(['error' => 'User not found'], 404);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Something went wrong', 'details' => $e->getMessage()], 500);
    }
}

public function updatepassword(Request $request)
{

    $user = User::find(auth()->id());
    $validator = Validator::make($request->all(), [
        'old_password' => 'required',
        'password' => 'required|min:6|confirmed']);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    if (!Hash::check($request->old_password, $user->password)) {
        return response()->json(['message' => 'Old password is incorrect'], 400);
    }
    $user->password = Hash::make($request->password);
    $user->save();

    return response()->json(['message' => 'Password changed successfully'], 200);
}


public function getUserCompres()
    {
        if (Auth::user()->role != 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $compres = ComPre::where('user_id', Auth::id())->pluck('name');

        return response()->json([
            'message' => 'Retrieved all compres created by the user',
            'Communities' => $compres
        ]);
    }
}