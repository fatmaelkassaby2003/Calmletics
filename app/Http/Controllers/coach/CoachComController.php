<?php

namespace App\Http\Controllers\coach;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ComPre;
use Illuminate\Support\Facades\Auth;
use App\Models\Card;
use App\Models\Plan;
use App\Models\Session;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CoachComController extends Controller
{

    public function getPlansByLevel(Request $request)
    {
        if (!Auth::check() || Auth::user()->role != 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        $request->validate([
            'level' => 'required|string|max:50',
        ]);
    
        $plans = Plan::with('sessions')->where('level', $request->level)->get();
    
        $plansWithContentStats = $plans->map(function ($plan) {
            $videoImage = asset('front/images/vidio-icon.png');
            $audioImage = asset('front/images/audio-icon.png');
            $pdfImage = asset('front/images/subtitle.png');
    
            $videoCount = 0;
            $audioCount = 0;
            $pdfCount = 0;
    
            foreach ($plan->sessions as $session) {
                $lower = strtolower($session->content);
    
                if (preg_match('/\.(mp4|mov|avi|mkv)|youtube\.com|vimeo\.com/', $lower)) {
                    $videoCount++;
                } elseif (preg_match('/\.(mp3|wav|ogg)/', $lower)) {
                    $audioCount++;
                } elseif (preg_match('/\.pdf/', $lower)) {
                    $pdfCount++;
                }
            }
    
            $total = $videoCount + $audioCount + $pdfCount;
    
            return [
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'sessions_count' => $plan->sessions->count(),
                'video_image' => $videoImage,
                'audio_image' => $audioImage,
                'pdf_image' => $pdfImage,
                'video_percentage' => $total ? round(($videoCount / $total) * 100) : 0,
                'audio_percentage' => $total ? round(($audioCount / $total) * 100) : 0,
                'pdf_percentage' => $total ? round(($pdfCount / $total) * 100) : 0,
            ];
        });
    
        return response()->json([
            'plans' => $plansWithContentStats
        ]);
    }
    
    public function createCompre(Request $request)
    {
        if (Auth::user()->role != 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|string|max:50',
            'plan_id' => 'required|exists:plans,id',
        ]);
    
        do {
            $code = rand(1000, 9999);
        } while (ComPre::where('code', $code)->exists());
    
        $compre = ComPre::create([
            'name' => $request->name,
            'level' => $request->level,
            'plan_id' => $request->plan_id,
            'code' => $code,
            'user_id' => Auth::id(), 
        ]);
    
        $now = \Carbon\Carbon::now();
        $createdLabel = $now->isToday() ? 'today' : 'last';
    
        return response()->json([
            'message' => "Community '{$compre->name}' created with code {$compre->code} ($createdLabel).",
            'data' => $compre
        ], 201);
    }    
    public function getSessionsByPlanId(Request $request)
    {
        if (!Auth::check() || Auth::user()->role != 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);
    
        $sessions = Session::where('plan_id', $request->plan_id)->get();
        $sessionsCount = $sessions->count();
    
        $sessionsData = $sessions->values()->map(function ($session, $index) {
            $lower = strtolower($session->content);
            $type = 'unknown';
            $icon = '';
    
            if (preg_match('/\.(mp4|mov|avi|mkv)|youtube\.com|vimeo\.com/', $lower)) {
                $type = 'video';
                $icon = asset('front/images/vidio-icon.png');
            } elseif (preg_match('/\.(mp3|wav|ogg)/', $lower)) {
                $type = 'audio';
                $icon = asset('front/images/audio-icon.png');
            } elseif (preg_match('/\.pdf/', $lower)) {
                $type = 'pdf';
                $icon = asset('front/images/subtitle.png');
            }
    
            return [
                'session_number' => 'Session ' . ($index + 1),
                'session_id' => $session->id,
                'session_name' => $session->name,
                'type' => $type,
                'icon' => $icon,
            ];
        });
    
        return response()->json([
            'sessions_count' => $sessionsCount,
            'sessions' => $sessionsData,
        ]);
    }
    

    public function storeCard(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:16|unique:cards,number',
            'date' => 'required|date_format:d-m-Y', // التحقق من أن التاريخ بصيغة صحيحة
            'cvv' => 'required|string|max:4',
        ]);
        $formattedDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
    
        $card = Card::create([
            'name' => $request->name,
            'number' => $request->number,
            'date' => $formattedDate,
            'cvv' => $request->cvv,
            'user_id' => Auth::id(),
        ]);
    
        return response()->json([
            'message' => 'Card saved successfully!',
            'data' => $card
        ], 201);
    }
    
    public function leaderboard(Request $request)
    {
        $user = User::find(auth()->id());
        $time = $request->time;
        $community_id = $request->community_id;
    
        $community = ComPre::find($community_id);
        if (!$community) {
            return response()->json(['error' => 'community not found'], 403);
        }
    
        // دالة لترتيب المستخدمين وإضافة rank
        $addRank = function ($collection) {
            $collection = $collection->values(); // تأكيد الفهرسة من 0
            foreach ($collection as $index => $item) {
                $item->rank = $index + 1;
            }
            return $collection;
        };
    
        // === daily leaderboard ===
        $topUsersByDay = DB::table('users')
            ->leftJoin('plandates', function ($join) {
                $join->on('users.id', '=', 'plandates.user_id')
                    ->whereDate('plandates.date', now()->toDateString());
            })
            ->select(
                'users.name', 'users.image', 'users.flag', 'users.com_pre_id',
                'users.id as user_id',
                DB::raw('COALESCE(SUM(plandates.score), 0) as total_score')
            )
            ->where('users.com_pre_id', $community_id)
            ->groupBy('users.id', 'users.name', 'users.image', 'users.flag', 'users.com_pre_id')
            ->orderByDesc('total_score')
            ->get();
    
        $topUsersByDay = $addRank($topUsersByDay);
    
        // === weekly leaderboard ===
        $topUsersByWeek = DB::table('users')
            ->leftJoin('plandates', function ($join) {
                $join->on('users.id', '=', 'plandates.user_id')
                    ->whereBetween('plandates.date', [now()->startOfWeek(), now()->endOfWeek()]);
            })
            ->select(
                'users.name', 'users.image', 'users.flag', 'users.com_pre_id',
                'users.id as user_id',
                DB::raw('COALESCE(SUM(plandates.score), 0) as total_score')
            )
            ->where('users.com_pre_id', $community_id)
            ->groupBy('users.id', 'users.name', 'users.image', 'users.flag', 'users.com_pre_id')
            ->orderByDesc('total_score')
            ->get();
    
        $topUsersByWeek = $addRank($topUsersByWeek);
    
        // === all time leaderboard ===
        $topUsersAllTime = DB::table('users')
            ->leftJoin('plandates', 'users.id', '=', 'plandates.user_id')
            ->select(
                'users.name', 'users.image', 'users.flag', 'users.com_pre_id',
                'users.id as user_id',
                DB::raw('COALESCE(SUM(plandates.score), 0) as total_score')
            )
            ->where('users.com_pre_id', $community_id)
            ->groupBy('users.id', 'users.name', 'users.image', 'users.flag', 'users.com_pre_id')
            ->orderByDesc('total_score')
            ->get();
    
        $topUsersAllTime = $addRank($topUsersAllTime);
    
        // === return based on time input ===
        if ($time === 'daily') {
            return response()->json(['users' => $topUsersByDay]);
        } elseif ($time === 'weekly') {
            return response()->json(['users' => $topUsersByWeek]);
        } else {
            return response()->json(['users' => $topUsersAllTime]);
        }
    }     
}

