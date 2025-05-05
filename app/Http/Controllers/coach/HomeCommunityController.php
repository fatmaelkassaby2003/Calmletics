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
  
    public function getCoachCommunities(Request $request)
{
    $coach = Auth::user();

    if (!$coach || $coach->role != 1) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $level = $request->query('level'); // القيمة ممكن تكون all, low, moderate, high أو null

    $communitiesQuery = Compre::withCount('users')
        ->where('user_id', $coach->id);

    // لو المستخدم طلب فلتر محدد غير all
    if ($level && $level !== 'all') {
        $communitiesQuery->where('level', $level);
    }

    $communities = $communitiesQuery->get();

    $data = $communities->map(function ($community) {
        return [
            'id' => $community->id,
            'name' => $community->name,
            'level' => $community->level,
            'players_count' => $community->users_count,
            'created_at' => $community->created_at->toDateTimeString(),
        ];
    });

    return response()->json(['communities' => $data]);
}



public function getCoachPlayersStatus(Request $request)
{
    $coach = Auth::user();

    if (!$coach || $coach->role != 1) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $communityIds = Compre::where('user_id', $coach->id)->pluck('id');
    
    $playersQuery = User::whereIn('com_pre_id', $communityIds);

    // استلام الفلتر من الـ request (all, missed, achievements)
    $statusFilter = $request->query('status'); 

    // فلترة اللاعبين بناءً على الحالة
    if ($statusFilter) {
        $now = \Carbon\Carbon::now();
        $playersQuery = $playersQuery->get()->filter(function ($player) use ($now, $statusFilter) {
            $lastDone = Doneplan::where('user_id', $player->id)
                ->where('done', true)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastDone) {
                $daysSinceDone = $lastDone->created_at->diffInDays($now);
            } else {
                $daysSinceDone = null;
            }

            // تطبيق الفلتر حسب الحالة المطلوبة
            if ($statusFilter === 'achievements' && $daysSinceDone <= 2) {
                return true; // يعرض اللاعبين الاكتيفين
            }

            if ($statusFilter === 'missed' && ($daysSinceDone > 2 || is_null($daysSinceDone))) {
                return true; // يعرض اللاعبين غير الاكتيفين
            }

            return false;
        });
    } else {
        // لو لم يتم تحديد الفلتر، نرجع كل اللاعبين
        $playersQuery = $playersQuery->get();
    }

    // تجهيز البيانات
    $data = $playersQuery->map(function ($player) use ($now) {
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
            $statusMessage = 'Player ' . $player->name . ' achieved a personal best in the Anxiety Test';
            $statusImage = asset('front/images/vector.png');
        } else {
            $statusMessage = 'Player ' . $player->name . ' hasn’t logged progress in the last 3 days';
            $statusImage = asset('front/images/icon.png');
        }

        return [
            'player_id' => $player->id,
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
