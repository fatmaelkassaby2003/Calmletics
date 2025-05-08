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
        if ($user->com_free_id ) {
            $plan_id = $user->comFree->plan_id;
        } else if ($user->com_pre_id) {
            $plan_id = $user->comPre->plan_id;
        }elseif ($user->plan_id) {
            $plan_id = $user->plan_id;
        }else {
            return response()->json([
                'message' => "user havn't plan ⏳"
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
    } else {
        DB::table('doneplans')->insert([
            'user_id' => $user->id,
            'plan_id' => $plan_id,
            'session_id' => $session_number,
            'done' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
         $planDate = Plandate::Create(
            ['date' => $today,
             'user_id' => $user->id, 
            'score' => 10]
         );
    
        return response()->json([
            'message' => 'Score saved successfully',
        ], 200);
    }
      
    public function getsessions(Request $request)
    {
        $user = User::find(auth()->id());
    
        // تحديد خطة المستخدم
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
    
        // جلب كل الجلسات المرتبطة بالخطة
        $contents = DB::table('sessions')
            ->where('plan_id', $plan_id)
            ->orderBy('id')
            ->get();
    
        if ($contents->isEmpty()) {
            return response()->json([
                'message' => 'No sessions found for this plan.'
            ]);
        }
    
        // معرفة الجلسات المكتملة للمستخدم
        $completedSessionIds = DB::table('doneplans')
            ->where('user_id', $user->id)
            ->where('done', true)
            ->pluck('session_id')
            ->toArray();
    
        // ترتيب الجلسات واستنتاج الجلسة التالية
        $nextSession = null;
        foreach ($contents as $session) {
            if (!in_array($session->id, $completedSessionIds)) {
                $nextSession = $session;
                break;
            }
        }
    
        // لو خلص كل الجلسات
        if (!$nextSession) {
            return response()->json([
                'message' => 'This plan has already been completed.'
            ]);
        }
    
        // تحديد نوع الجلسة المفتوحة حاليًا
        if ($nextSession->type == 1) {
            $content_type = asset('front/images/vr.png');
        } elseif (Str::endsWith($nextSession->content, '.mp3')) {
            $content_type = asset('front/images/audio-icon.png');
        } elseif (Str::endsWith($nextSession->content, '.mp4')) {
            $content_type = asset('front/images/vidio-icon.png');
        } else {
            $content_type = asset('front/images/subtitle.png');
        }
    
        // بناء قائمة الجلسات مع الحالة
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
                'session_number' => "Session $order",
                'status' => $status,
            ];
        }
    
        // حساب النسبة
        $count = $contents->count();
        $completedCount = count($completedSessionIds);
        $percentage = $count > 0 ? ($completedCount / $count) * 100 : 0;
    
        // حفظ النسبة
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
        $key_active = false;
        if($content->type == 1 && $user->com_pre_id != null){
            $key_active = true;
        }
        return response()->json([
            'content' => $content->content,
            'key_active' => $key_active
        ], 200);
    }
}
