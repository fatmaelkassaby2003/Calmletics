<?php

namespace App\Http\Controllers\coach;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ComPre;
use Illuminate\Support\Facades\Auth;



class CommunityDetailsController extends Controller
{
    public function getCommunityDetails(Request $request)
{
    $coach = Auth::user();

    if (!$coach || $coach->role != 1) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $communityId = $request->input('community_id');

    if (!$communityId) {
        return response()->json(['error' => 'Community ID is required'], 400);
    }

    $community = ComPre::where('id', $communityId)
                        ->where('user_id', $coach->id)
                        ->first();

    if (!$community) {
        return response()->json(['error' => 'Community not found or unauthorized'], 404);
    }

    $plan = $community->plan;

    if (!$plan) {
        return response()->json(['error' => 'No plan associated with this community'], 404);
    }

    $sessions = $plan->sessions ?? [];

    $sessionData = collect($sessions)->values()->map(function ($session, $index) {
        return 'Session ' . ($index + 1) . ': ' . $session->name;
    });

    return response()->json([
        'community_name' => $community->name,
        'community_code' => $community->code,
        'sessions' => $sessionData
    ]);
}

}
