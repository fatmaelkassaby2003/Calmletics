<?php

namespace App\Http\Controllers\player;

use App\Http\Controllers\Controller;
use App\Models\ComPre;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PreCommunityController extends Controller
{
    public function join(Request $request)
    {
        $user = User::find(auth()->id());
    
        $validator = Validator::make($request->all(), [
            'code' => 'required|integer|digits:4',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $compre = ComPre::where('code', $request->code)->first();
    
        if (!$compre) {
            return response()->json(['error' => 'Community not found'], 400);
        }
    
        $user->com_pre_id = $compre->id;
    
        if ($user->com_free_id !== null) {
            $user->com_free_id = null;
        }
    
        $user->save();
    
        return response()->json([
            'message' => 'Joined successfully',
            'community_name' => $compre->name,
            'user' => $user
        ]);
    }
    
    public function availableDates()
    {
        $dates = [];
        $startTime = Carbon::createFromTime(9, 0, 0); // 9:00 AM
        $endTime = Carbon::createFromTime(15, 0, 0);  // 3:00 PM

        for ($i = 0; $i <= 6; $i++) {
            $carbonDate = Carbon::today()->addDays($i);
            
            $date = $carbonDate->format('M Y'); // شهر وسنة فقط
            $day = $carbonDate->format('D, d'); // اليوم والتاريخ فقط
            
            $timeSlots = [];
            $currentTime = clone $startTime;

            while ($currentTime < $endTime) {
                $timeSlots[] = [
                    'start_time' => $currentTime->format('h:i A'), // 12 ساعة
                    'end_time' => $currentTime->copy()->addHour()->format('h:i A'),
                ];
                $currentTime->addHour();
            }

            $dates[] = [
                'date' => $date, 
                'day' => $day,   
                'slots' => $timeSlots
            ];
        }

        return response()->json($dates);
    }
    public function leaderboard(Request $request)
    {
        $user = User::find(auth()->id());
        $time = $request->time;
        if(!$user->com_pre_id){
            return response()->json(['error' => 'User is not part of premium community'], 403);
            
        }
        $topUsersByDay = DB::table('users')
            ->leftJoin('plandates', function ($join) {
                $join->on('users.id', '=', 'plandates.user_id')
                ->whereDate('plandates.date', now()->toDateString());})
            ->select('users.name','users.image','users.flag','users.com_pre_id','users.id as user_id',
            DB::raw('COALESCE(SUM(plandates.score), 0) as total_score'))
            ->where('users.com_pre_id', $user->com_pre_id)
            ->groupBy('users.id', 'users.name', 'users.image', 'users.flag', 'users.com_pre_id')
            ->orderByDesc('total_score')
            ->get();
        
        $topUsersByWeek = DB::table('users')
            ->leftJoin('plandates', function ($join) {
                $join->on('users.id', '=', 'plandates.user_id')
                ->whereBetween('plandates.date', [now()->startOfWeek(), now()->endOfWeek()]);})
            ->select('users.name','users.image','users.flag','users.com_pre_id','users.id as user_id',
            DB::raw('COALESCE(SUM(plandates.score), 0) as total_score')
            )
            ->where('users.com_pre_id', $user->com_pre_id)
            ->groupBy('users.id', 'users.name', 'users.image', 'users.flag', 'users.com_pre_id')
            ->orderByDesc('total_score')
            ->get();
        
        $topUsersAllTime = DB::table('users')
            ->leftJoin('plandates', 'users.id', '=', 'plandates.user_id')
            ->select('users.name','users.image','users.flag','users.com_pre_id','users.id as user_id',
            DB::raw('COALESCE(SUM(plandates.score), 0) as total_score'))
            ->where('users.com_pre_id', $user->com_pre_id)
            ->groupBy('users.id', 'users.name', 'users.image', 'users.flag', 'users.com_pre_id')
            ->orderByDesc('total_score')
            ->get();     
        if ($time === 'daily') {
            return response()->json(['user_id' => $user->id,'users' => $topUsersByDay]);
        } elseif ($time === 'weekly') {
            return response()->json(['user_id' => $user->id,'users' => $topUsersByWeek]);
        } else {
            return response()->json(['user_id' => $user->id,'users' => $topUsersAllTime]);
        }
    }
}
