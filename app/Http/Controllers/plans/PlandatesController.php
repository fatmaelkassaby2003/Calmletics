<?php

namespace App\Http\Controllers\plans;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class PlandatesController extends Controller
{
    // public function storeScore(Request $request)
    // {
    //     $request->validate([
    //         'score' => 'required|integer|min:0'
    //     ]);

    //     $user = Auth::user();
    //     if (!$user) {
    //         return response()->json(['message' => 'Unauthorized'], 401);
    //     }

    //     $today = Carbon::today()->toDateString();

    //     // $planDate = PlanDate::updateOrCreate(
    //     //     ['date' => $today, 'user_id' => $user->id], 
    //     //     ['score' => $request->score]
    //     // );
    //        $planDate = PlanDate::Create(
    //         ['date' => $today,
    //          'user_id' => $user->id, 
    //         'score' => $request->score]
    //      );


    //     return response()->json([
    //         'message' => 'Score saved successfully',
    //         'data' => $planDate
    //     ], 200);
    // }

    public function getScoresForLast7Days()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $today = Carbon::today();
        $lastSevenDays = collect();

        for ($i = 0; $i < 7; $i++) {
            $date = $today->subDay($i)->toDateString();
            $score = PlanDate::where('date', $date)->where('user_id', $user->id)->first();

            $lastSevenDays->push([
                'date' => $date,
                'score' => $score ? $score->score : null
            ]);
        }

        return response()->json([
            'message' => 'Scores retrieved successfully',
            'data' => $lastSevenDays
        ], 200);
    }


    public function assignPlanToCurrentUser(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);
    
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
    
        $user->update([
            'plan_id' => $request->plan_id
        ]);
    
        return response()->json([
            'message' => 'Plan assigned successfully',
            'user' => $user->fresh(),
        ]);
    }
}


