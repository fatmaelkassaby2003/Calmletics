<?php

namespace App\Http\Controllers\player;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ComFree;

class FreeCommunityController extends Controller
{
    public function join(Request $request)
{
    $user = User::find(auth()->id());

    if (!$user || $user->cluster === null) {
        return response()->json(['error' => 'Cluster not assigned to user'], 400);
    }

    $communityId = $user->cluster + 1;
    $community = ComFree::find($communityId);

    if (!$community) {
        return response()->json(['error' => 'No matching community found'], 404);
    }

    $user->com_free_id = $community->id;

    if ($user->com_pre_id !== null) {
        $user->com_pre_id = null;
    }

    $user->save();

    return response()->json([
        'message' => 'Joined successfully',
        'community_name' => $community->name
    ], 200);
}


    public function community(Request $request)
    {
        $user = User::find(auth()->id());
        $com_free_id = $user->comfree->id;
        $users = user::where('com_free_id', $com_free_id)->where('id', '!=', $user->id)->select('name','image')->get();
        return response()->json($users);
    }
    
    public function leaderboard(Request $request)
    {
        $user = User::find(auth()->id());
        $time = $request->time;
    
        if (!$user->com_free_id) {
            return response()->json(['error' => 'User is not part of free community'], 403);
        }
    
        $addRank = function ($collection) {
            $collection = $collection->values(); 
            foreach ($collection as $index => $item) {
                $item->rank = $index + 1;
            }
            return $collection;
        };
    
        $topUsersByDay = DB::table('users')
            ->leftJoin('plandates', function ($join) {
                $join->on('users.id', '=', 'plandates.user_id')
                    ->whereDate('plandates.date', now()->toDateString());
            })
            ->select(
                'users.name', 'users.image', 'users.flag', 'users.com_free_id',
                'users.id as user_id',
                DB::raw('COALESCE(SUM(plandates.score), 0) as total_score')
            )
            ->where('users.com_free_id', $user->com_free_id)
            ->groupBy('users.id', 'users.name', 'users.image', 'users.flag', 'users.com_free_id')
            ->orderByDesc('total_score')
            ->get();
    
        $topUsersByDay = $addRank($topUsersByDay);
    
        $topUsersByWeek = DB::table('users')
            ->leftJoin('plandates', function ($join) {
                $join->on('users.id', '=', 'plandates.user_id')
                    ->whereBetween('plandates.date', [now()->startOfWeek(), now()->endOfWeek()]);
            })
            ->select(
                'users.name', 'users.image', 'users.flag', 'users.com_free_id',
                'users.id as user_id',
                DB::raw('COALESCE(SUM(plandates.score), 0) as total_score')
            )
            ->where('users.com_free_id', $user->com_free_id)
            ->groupBy('users.id', 'users.name', 'users.image', 'users.flag', 'users.com_free_id')
            ->orderByDesc('total_score')
            ->get();
    
        $topUsersByWeek = $addRank($topUsersByWeek);
    
        $topUsersAllTime = DB::table('users')
            ->leftJoin('plandates', 'users.id', '=', 'plandates.user_id')
            ->select(
                'users.name', 'users.image', 'users.flag', 'users.com_free_id',
                'users.id as user_id',
                DB::raw('COALESCE(SUM(plandates.score), 0) as total_score')
            )
            ->where('users.com_free_id', $user->com_free_id)
            ->groupBy('users.id', 'users.name', 'users.image', 'users.flag', 'users.com_free_id')
            ->orderByDesc('total_score')
            ->get();
    
        $topUsersAllTime = $addRank($topUsersAllTime);
    
        if ($time === 'daily') {
            return response()->json([
                'user_id' => $user->id,
                'users' => $topUsersByDay
            ]);
        } elseif ($time === 'weekly') {
            return response()->json([
                'user_id' => $user->id,
                'users' => $topUsersByWeek
            ]);
        } else {
            return response()->json([
                'user_id' => $user->id,
                'users' => $topUsersAllTime
            ]);
        }
    }
    

}