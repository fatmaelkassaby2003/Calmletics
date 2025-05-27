<?php

namespace App\Http\Controllers\plans;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Plandate;
use App\Models\User;

class DoneplaneController extends Controller
{

    public function updateProgress(Request $request)
    {
        $user = auth()->user();
        $session_number = $request->session_id;
    
        $request->validate([
            'session_id' => 'required|integer',
            'note' => 'nullable|string|max:1000',
        ]);
    
        if ($user->com_free_id) {
            $plan_id = $user->comFree->plan_id;
        } elseif ($user->com_pre_id) {
            $plan_id = $user->comPre->plan_id;
        } elseif ($user->plan_id) {
            $plan_id = $user->plan_id;
        } else {
            return response()->json([
                'message' => "user hasn't plan ⏳"
            ], 403);
        }
    
        $today = Carbon::today()->toDateString();
    
        $exists = DB::table('doneplans')
            ->where('user_id', $user->id)
            ->where('session_id', $session_number)
            ->exists();
    
        if ($exists) {
            return response()->json([
                'message' => 'This content has already been completed.'
            ], 400);
        }
    
        DB::table('doneplans')->insert([
            'user_id' => $user->id,
            'plan_id' => $plan_id,
            'session_id' => $session_number,
            'done' => true,
            'note' => $request->note,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        Plandate::create([
            'date' => $today,
            'user_id' => $user->id,
            'score' => 10,
        ]);
    
        return response()->json([
            'message' => 'Score and feedback saved successfully',
        ], 200);
    }
        public function getsessions(Request $request)
    {
        $user = User::find(auth()->id());
    
        if ($user->com_free_id) {
            $plan_id = $user->comFree->plan_id;
        } elseif ($user->com_pre_id) {
            $plan_id = $user->comPre->plan_id;
        } elseif ($user->plan_id) {
            $plan_id = $user->plan_id;
        } else {
            return response()->json([
                'message' => "User doesn't have a plan ⏳"
            ], 403);
        }
    
        $contents = DB::table('sessions')
            ->where('plan_id', $plan_id)
            ->orderBy('id')
            ->get();
    
        if ($contents->isEmpty()) {
            return response()->json([
                'message' => 'No sessions found for this plan.'
            ]);
        }
    
        $completedSessionIds = DB::table('doneplans')
            ->where('user_id', $user->id)
            ->where('done', true)
            ->pluck('session_id')
            ->toArray();
    
        $nextSession = null;
        foreach ($contents as $session) {
            if (!in_array($session->id, $completedSessionIds)) {
                $nextSession = $session;
                break;
            }
        }
    
        if (!$nextSession) {
            return response()->json([
                'message' => 'This plan has already been completed.'
            ]);
        }
    
        if ($nextSession->type == 1) {
            $content_type = asset('front/images/vr.png');
        } elseif (Str::endsWith($nextSession->content, '.mp3')) {
            $content_type = asset('front/images/audio-icon.png');
        } elseif (Str::endsWith($nextSession->content, '.mp4')) {
            $content_type = asset('front/images/vidio-icon.png');
        } else {
            $content_type = asset('front/images/subtitle.png');
        }
    
        $numberedList = [];
        foreach ($contents as $i => $session) {
            $order = $i + 1;
    
            if (in_array($session->id, $completedSessionIds)) {
                $status = asset('front/images/done-icon.png');
            } elseif ($session->id == $nextSession->id) {
                $status = $content_type;
            } else {
                $status = asset('front/images/lock-icon.png');
            }
    
            $numberedList[] = [
                'session_id' => $session->id,
                'session_name' => $session->name,
                'session_type' => $session->type,
                'session_number' => "Session $order",
                'status' => $status,
            ];
        }
    
        $count = $contents->count();
        $completedCount = count($completedSessionIds);
        $percentage = $count > 0 ? ($completedCount / $count) * 100 : 0;
    
        $user->Percentage = $percentage;
        $user->save();
    
        return response()->json([
            'count' => $count,
            'Percentage' => round($percentage, 2) . ' %',
            'session_list' => $numberedList
        ]);
    }
    
    public function getsession_content(Request $request){
        $user = auth()->user();
        $content_id = $request->session_id;
        $content = DB::table('sessions')
        ->where('id', $content_id)
        ->first();
        if (!$content) {
            return response()->json([
                'message' => 'no content here.'
            ]);
        }
        $booksession = DB::table('session_books')
        ->where('session_id', $content_id)
        ->where('user_id', $user->id)
        ->select( 'day', 'time')
        ->first();
        $key_active = false;
        if($content->type == 1 && $user->com_pre_id != null){
            $key_active = true;
        }
        return response()->json([
            'session_id' => $content->id,
            'content' => $content->content,
            'task' => $content->task,
            'practical' => $content->practical,
            'key_active' => $key_active,
            'booksession' => $booksession
        ], 200);
    }

    public function booksession(Request $request){
        $user = auth()->user();
        $session_id = $request->session_id;
        $year = $request->year;
        $day = $request->day;
        $time = $request->time;
        $booksession = DB::table('session_books')
        ->where('year', $year)
        ->where('day', $day)
        ->where('time', $time)
        ->first();
        if($booksession){
            return response()->json([
                'message' => 'this session is already booked.'
            ]);
        }
        DB::table('session_books')->insert([
            'user_id' => $user->id,
            'session_id' => $session_id,
            'year' => $year,
            'day' => $day,
            'time' => $time,
        ]);
        return response()->json([
            'message' => 'booked session successfully.'
        ], 200);
    }
    public function gettasks(Request $request)
    {
        $user = User::find(auth()->id());
    
        if ($user->com_free_id) {
            $plan_id = $user->comFree->plan_id;
        } elseif ($user->com_pre_id) {
            $plan_id = $user->comPre->plan_id;
        } elseif ($user->plan_id) {
            $plan_id = $user->plan_id;
        } else {
            return response()->json([
                'message' => "User doesn't have a plan ⏳"
            ], 403);
        }
            $contents = DB::table('sessions')
            ->where('plan_id', $plan_id)
            ->orderBy('id')
            ->get();
    
        if ($contents->isEmpty()) {
            return response()->json([
                'message' => 'No sessions found for this plan.'
            ]);
        }
            $completedSessions = DB::table('doneplans')
            ->where('user_id', $user->id)
            ->where('done', true)
            ->get();
    
        $sessions = [];
        foreach ($contents as $session) {
            $completedSession = $completedSessions->firstWhere('session_id', $session->id);
            if ($completedSession) {
                $sessions[] = [
                    'session_id' => $session->id,
                    'session_name' => $session->name,
                    'task' => $session->task,
                    'practical' => $session->practical,
                    'feeling' => $completedSession->feeling,  
                    'status' => 'done'
                ];
            } elseif (!isset($nextSession)) {
                $nextSession = $session;
            }
        }
        if (isset($nextSession)) {
            $sessions[] = [
                'session_id' => $nextSession->id,
                'session_name' => $nextSession->name,
                'task' => $nextSession->task,
                'practical' => $nextSession->practical,
                'feeling' => null,
                'status' => 'active'
            ];
        }
    
        return response()->json([
            'message' => 'All tasks:',
            'tasks' => $sessions
        ]);
    }
    public function getProgress(Request $request)
{

    $user = User::find(auth()->id());

    if ($user->com_free_id) {
        $plan_id = $user->comFree->plan_id;
    } elseif ($user->com_pre_id) {
        $plan_id = $user->comPre->plan_id;
    } elseif ($user->plan_id) {
        $plan_id = $user->plan_id;
    } else {
        return response()->json([
            'message' => "User doesn't have a plan ⏳"
        ], 403);
    }

    // جلب الجلسات مرتبة حسب id زي ما في getsessions
    $sessions = DB::table('sessions')
        ->where('plan_id', $plan_id)
        ->orderBy('id')
        ->get();

    if ($sessions->isEmpty()) {
        return response()->json([
            'message' => 'No sessions found for this plan.'
        ]);
    }

    // جلب الجلسات المنتهية
    $completedSessionIds = DB::table('doneplans')
        ->where('user_id', $user->id)
        ->where('done', true)
        ->pluck('session_id')
        ->toArray();

    $nextSession = null;
    $sessionNumber = null;

    foreach ($sessions as $index => $session) {
        if (!in_array($session->id, $completedSessionIds)) {
            $nextSession = $session;
            $sessionNumber = $index + 1; // نفس منطق getsessions
            break;
        }
    }

    if (!$nextSession) {
        return response()->json([
            'message' => 'This plan has already been completed.'
        ]);
    }

    // حساب نسبة التقدم
    $total = $sessions->count();
    $completed = count($completedSessionIds);
    $percentage = $total > 0 ? ($completed / $total) * 100 : 0;

    $user->Percentage = $percentage;
    $user->save();

    return response()->json([
        'your plan' => [
            'session_id' => $session->id,
            'session_number' => $sessionNumber,
            'session_name' => $nextSession->name,
            'Percentage' => round($percentage, 2) . ' %'  
        ],
        "task's today" => [
           'progress' =>  "$completed of $total tasks completed",
            'Percentage' => round($percentage, 2) . ' %'  
        ],       
    ]);
}    
}
