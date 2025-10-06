<?php

namespace App\Http\Controllers\player;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Answer;

class HomeController extends Controller
{
    public function getScore(Request $request)
    {
        $user = User::find(auth()->id());
    
        if (!$user) {
            return response()->json(['error' => 'Unauthorized. Token might be invalid or expired.'], 401);
        }
    
        $validator = Validator::make($request->all(), [
            'score' => 'required|integer|min:0|max:100',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $user->score = $request->score;
        $user->save();
    
        $level = ($user->score < 34) ? 'Low' : (($user->score > 66) ? 'High' : 'Moderate');
    
        $answer = Answer::where('user_id', $user->id)->first();
        if ($answer) {
            $answer->anxiety_level = $level;
            $answer->save();
        }
        return response()->json([
            'message' => 'Score stored successfully',
            'score' => $user->score,
            'level' => $level,
        ], 200);
    }
    
    // public function userPlan (){
    //     $user = User::find(auth()->id());
    //     if ($user->com_free_id ) {
    //         $plan_id = $user->comFree->plan_id;
    //     } else if ($user->com_pre_id) {
    //         $plan_id = $user->comPre->plan_id;
    //     }elseif ($user->plan_id) {
    //         $plan_id = $user->plan_id;
    //     }else {
    //         return response()->json([
    //             'message' => "user havn't plan ⏳"
    //         ], 403);
    //     }

    //     return response()->json($plan);
    // }


    public function image(Request $request)
    {
        $user = User::find(auth()->id());
        
        $validator = Validator::make($request->all(), [
            'image' => 'required|', 
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $user->image = $request->image;
        $user->save();

        return response()->json(['message' => 'Image stored successfully'], 200);
    }
    public function flag(Request $request)
    {
        $user = User::find(auth()->id());
        $validator = Validator::make($request->all(), [
            'flag' => 'required|', 
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $user->flag = $request->flag;
        $user->save();

        return response()->json(['message' => 'flag stored successfully'], 200);
    }

    public function Cluster(Request $request)
{
    $request->validate([
        'cluster' => 'required|numeric',
    ]);

    $user = User::find(auth()->id());

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $user->cluster = $request->cluster;
    $user->save();

    return response()->json([
        'message' => 'Cluster updated successfully',
        'user' => [
            'id' => $user->id,
            'cluster' => $user->cluster,
        ]
    ], 200);
}


public function recommended(Request $request)
{
    $request->validate([
        'recommended_plan_id' => 'required|numeric',
    ]);

    $user = User::find(auth()->id());

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $recommendedPlanId = $request->recommended_plan_id;

    if ($recommendedPlanId < 10) {
        $user->plan_id = 1;
    } elseif ($recommendedPlanId >= 10 && $recommendedPlanId <= 18) {
        $user->plan_id = 10;
    } else {
        $user->plan_id = 19;
    }

    $user->save();

    return response()->json([
        'message' => 'Plan updated successfully',
        'user' => [
            'id' => $user->id,
            'plan_id' => $user->plan_id,
        ]
    ], 200);
}


}