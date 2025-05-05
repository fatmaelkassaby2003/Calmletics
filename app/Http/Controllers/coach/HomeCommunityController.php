<?php

namespace App\Http\Controllers\coach;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ComPre;
use App\Models\Doneplan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class HomeCommunityController extends Controller
{
  
    public function getCoachCommunities()
    {
        $coach = Auth::user();
    
        if (!$coach || $coach->role != 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        $communities = Compre::withCount('users')
            ->where('user_id', $coach->id)
            ->get();
    
        $data = $communities->map(function ($community) {
            return [
                'name' => $community->name,
                'level' => $community->level,
                'players_count' => $community->users_count,
                'created_at' => $community->created_at->toDateTimeString(),
            ];
        });
    
        return response()->json(['communities' => $data]);
    }


    public function getCoachPlayersStatus()
{
    $coach = Auth::user();

    if (!$coach || $coach->role != 1) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $communityIds = Compre::where('user_id', $coach->id)->pluck('id');

    $players = User::whereIn('com_pre_id', $communityIds)->get();

    $now = \Carbon\Carbon::now();

    $data = $players->map(function ($player) use ($now) {
        $communityName = optional($player->compre)->name ?? 'غير معروف';
        $lastDone = Doneplan::where('user_id', $player->id)
                    ->where('done', true)
                    ->orderBy('created_at', 'desc')
                    ->first();

        if ($lastDone) {
            $daysSinceDone = $lastDone->created_at->diffInDays($now);
        } else {
            $daysSinceDone = null;
        }

        if (!is_null($daysSinceDone) && $daysSinceDone <= 2) {
            $statusMessage = 'Player Alex Taylor achieved a personal best in the Anxiety Test';
            $statusImage = asset('front/images/vector.png');
        } else {
            $statusMessage = 'hasn’t logged progress in the last 3 days';
            $statusImage = asset('front/images/icon.png');
        }

        return [
            'player_name' => $player->name,
            'community_name' => $communityName,
            'status_message' => $statusMessage,
            'status_image' => $statusImage,
        ];
    });

    return response()->json(['players' => $data]);
}

public function getPremiumCommunityPlayersCount()
{
    $coach = Auth::user();

    if (!$coach || $coach->role != 1) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $communityIds = Compre::where('user_id', $coach->id)->pluck('id');

    $playerCount = User::whereIn('com_pre_id', $communityIds)->count();

    return response()->json([
        'player_count' => $playerCount
    ]);
}

}
