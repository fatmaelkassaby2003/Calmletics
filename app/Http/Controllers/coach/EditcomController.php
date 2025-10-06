<?php

namespace App\Http\Controllers\coach;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ComPre;
use App\Models\User;
use Illuminate\Http\Request;

class EditcomController extends Controller
{
    public function deleteCommunity(Request $request)
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

    $community->delete();

    return response()->json(['message' =>$community->name . ' deleted successfully']);
}

public function updateCommunityName(Request $request)
{
    $coach = Auth::user();

    if (!$coach || $coach->role != 1) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $communityId = $request->input('community_id');
    $newName = $request->input('new_name');

    if (!$communityId || !$newName) {
        return response()->json(['error' => 'Community ID and new name are required'], 400);
    }

    $community = ComPre::where('id', $communityId)
                        ->where('user_id', $coach->id)
                        ->first();

    if (!$community) {
        return response()->json(['error' => 'Community not found or unauthorized'], 404);
    }

    $oldName = $community->name;
    $community->name = $newName;
    $community->save();

    $now = \Carbon\Carbon::now();
    $label = $now->isToday() ? 'today' : 'last';

    return response()->json([
        'message' => "Community name changed from '$oldName' to '$newName' successfully ($label)."
    ]);
}

public function removePlayerFromCommunity(Request $request)
{
    $coach = Auth::user();

    if (!$coach || $coach->role != 1) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $playerId = $request->input('player_id');

    $player = User::where('id', $playerId)
                  ->whereHas('compre', function ($query) use ($coach) {
                      $query->where('user_id', $coach->id);
                  })->first();

    if (!$player) {
        return response()->json(['error' => 'Player not found or not in your community'], 404);
    }

    $communityName = optional($player->compre)->name ?? 'Unknown Community';
    $playerName = $player->name;

    $player->com_pre_id = null;
    $player->save();

    $now = \Carbon\Carbon::now();
    $label = $now->isToday() ? 'today' : 'last';

    return response()->json([
        'message' => "Player '$playerName' has been removed from the premium community '$communityName' successfully ($label)."
    ]);
}

}
