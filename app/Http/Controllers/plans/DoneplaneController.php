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
   
        $lastCompleted = DB::table('doneplans')
            ->where('user_id', $user->id)
            ->where('done', true)
            ->max('session_id');
    
        $nextContentNumber = $lastCompleted ? $lastCompleted + 1 : 1;
        $content = DB::table('sessions')
        ->where('plan_id', $plan_id)
        ->where('id', $nextContentNumber)
        ->first();
        $content_type = asset('front/images/subtitle.png'); 
        if (Str::endsWith($content->content, '.mp3')) {
            $content_type = asset('/front/images/audio-icon.png');
        } elseif (Str::endsWith($content->content, '.mp4')) {
            $content_type = asset('/front/images/vidio-icon.png');
        }
        $contents = DB::table('sessions')
        ->where('plan_id', $plan_id)
        ->orderBy('id')
        ->get();
        
        $numberedList = [];
        
        foreach ($contents as $index => $session) {
            $sessionNumber = $session->id;
            $sessionName = $session->name;
            $order = $index + 1;

            if ($order < $nextContentNumber) {  
                $status = asset('front/images/done-icon.png');
            } elseif ($order == $nextContentNumber) {
                $status = $content_type;
        } else {
            $status = asset('front/images/lock-icon.png');
        }

        $numberedList[] = [
            'session_id' => $sessionNumber,
            'session_name' => $sessionName,
            'session_number' => "session $order",
            'status' => $status,
        ];
    }
    
    $completedSessionsCount = DB::table('doneplans')
    ->where('user_id', $user->id)
    ->where('done', true)
    ->count();
    $count = $contents->count();
    $Percentage = ($completedSessionsCount / $count) * 100;
    $user->Percentage = $Percentage;
    $user->save();
    return response()->json([
        'count' => $count,
        'Percentage' => $Percentage . ' %',
        'session_list' => $numberedList
        ]);
    }
    public function getsession_content(Request $request){
        $user = auth()->user();
        $content_id = $request->session_id;
        $content = DB::table('sessions')
        ->where('id', $content_id)
        ->first();
        return response()->json([
            'content' => $content->content
        ], 200);
    }
}
