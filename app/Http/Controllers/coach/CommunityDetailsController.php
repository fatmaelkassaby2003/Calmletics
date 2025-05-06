<?php

namespace App\Http\Controllers\coach;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ComPre;
use App\Models\Doneplan;
use App\Models\User;
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

    // جلب جميع اللاعبين داخل الكومينتي باستثناء الكوتش نفسه
    $players = User::where('com_pre_id', $community->id)
                    ->where('id', '!=', $coach->id)
                    ->get(['id', 'name']);

    $playersCount = $players->count();

    $plan = $community->plan;

    $sessions = $plan ? ($plan->sessions ?? []) : [];

    $sessionData = collect($sessions)->values()->map(function ($session, $index) use ($players, $playersCount) {
        // حساب عدد اللاعبين الذين أتموا هذه الجلسة
        $doneCount = Doneplan::where('session_id', $session->id)
                             ->where('done', true)
                             ->whereIn('user_id', $players->pluck('id'))
                             ->count();

        // تجنب القسمة على صفر
        $percentage = $playersCount > 0 ? round(($doneCount / $playersCount) * 100, 2) : 0;

        return [
            'session_number' => 'Session ' . ($index + 1),
            'session_name' => $session->name,
            'completion_percentage' => $percentage . '%'
        ];
    });

    return response()->json([
        'community_id' => $community->id,
        'community_name' => $community->name,
        'community_code' => $community->code,
        'players_count' => $playersCount,
        'players' => $players,
        'sessions' => $sessionData
    ]);
}

public function getCommunityPlayersStatus(Request $request)
{
    $coach = Auth::user();

    if (!$coach || $coach->role != 1) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $communityId = $request->query('community_id');
    $statusFilter = $request->query('status');
    $now = \Carbon\Carbon::now();

    $community = Compre::where('id', $communityId)
                        ->where('user_id', $coach->id)
                        ->first();

    if (!$community) {
        return response()->json(['error' => 'Community not found or unauthorized'], 404);
    }

    $playersQuery = User::where('com_pre_id', $community->id);

    if ($statusFilter) {
        $playersQuery = $playersQuery->get()->filter(function ($player) use ($now, $statusFilter) {
            $lastDone = Doneplan::where('user_id', $player->id)
                ->where('done', true)
                ->orderBy('created_at', 'desc')
                ->first();

            $daysSinceDone = $lastDone ? $lastDone->created_at->diffInDays($now) : null;

            if ($statusFilter === 'achievements' && $daysSinceDone <= 2) {
                return true;
            }

            if ($statusFilter === 'missed' && ($daysSinceDone > 2 || is_null($daysSinceDone))) {
                return true;
            }

            return false;
        });
    } else {
        $playersQuery = $playersQuery->get();
    }

    $data = $playersQuery->map(function ($player) use ($now, $community) {
        $lastDone = Doneplan::where('user_id', $player->id)
                    ->where('done', true)
                    ->orderBy('created_at', 'desc')
                    ->first();

        $daysSinceDone = $lastDone ? $lastDone->created_at->diffInDays($now) : null;

        if (!is_null($daysSinceDone) && $daysSinceDone <= 2) {
            $statusMessage = 'Player ' . $player->name . ' achieved a personal best in the Anxiety Test';
            $statusImage = asset('front/images/vector.png');
        } else {
            $statusMessage = 'Player ' . $player->name . ' hasn’t logged progress in the last 3 days';
            $statusImage = asset('front/images/icon.png');
        }

        return [
            'player_id' => $player->id,
            'player_name' => $player->name,
            'community_name' => $community->name,
            'status_message' => $statusMessage,
            'status_image' => $statusImage,
        ];
    });

    return response()->json(['players' => $data]);
}

}
