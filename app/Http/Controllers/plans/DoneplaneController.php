<?php

namespace App\Http\Controllers\plans;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Plandate;



class DoneplaneController extends Controller
{

    public function updateProgress(Request $request)
    {
        $user = auth()->user();
        $contentNumber = $request->content_number;

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


            $currentWeek = now()->format('oW'); 
            $today = Carbon::today()->toDateString();



        $completedThisWeek = DB::table('doneplans')
            ->where('user_id', $user->id)
            ->where('done', true) 
            ->whereRaw("YEARWEEK(created_at, 1) = ?", [$currentWeek]) 
            ->count();
    
        if ($completedThisWeek >= 2) {
            return response()->json([
                'message' => 'لقد وصلت إلى الحد الأقصى لهذا الأسبوع. يرجى الانتظار حتى الأسبوع القادم ⏳'
            ], 403);
        }
    
        $exists = DB::table('doneplans')
            ->where('user_id', $user->id)
            ->where('content_number', $contentNumber)
            ->exists();
    
        if (!$exists) {
            DB::table('doneplans')->insert([
                'user_id' => $user->id,
                'plan_id' => $plan_id,
                'content_number' => $contentNumber,
                'done' => true, // يتم تحديد أن المحتوى مكتمل
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
    
    public function getNextContent(Request $request)
    {
        $user = auth()->user();
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
        $currentWeek = now()->format('oW');
        $completedThisWeek = DB::table('doneplans')
            ->where('user_id', $user->id)
            ->where('done', true)
            ->whereRaw("YEARWEEK(created_at, 1) = ?", [$currentWeek])
            ->count();
    
        if ($completedThisWeek >= 2) {
            return response()->json([
                'message' => 'لقد وصلت إلى الحد الأقصى لهذا الأسبوع. انتظر حتى الأسبوع القادم ⏳'
            ], 403);
        }
    
        $lastCompleted = DB::table('doneplans')
            ->where('user_id', $user->id)
            ->where('done', true)
            ->max('content_number');
    
        $nextContentNumber = $lastCompleted ? $lastCompleted + 1 : 1;
    
        $alreadyExists = DB::table('doneplans')
            ->where('user_id', $user->id)
            ->where('content_number', $nextContentNumber)
            ->exists();
    
        if ($alreadyExists) {
            return response()->json([
                'message' => 'هذا المحتوى قد تم تسجيله بالفعل.'
            ], 400);
        }
    
        $content = DB::table('plans')
            ->where('id', $plan_id)
            ->selectRaw("content$nextContentNumber as content")
            ->first();
    
        if (!$content || empty($content->content)) {
            return response()->json([
                'message' => 'لقد أكملت جميع المحتويات لهذه الخطة 🎉'
            ]);
        }
    
        return response()->json([
            'content_number' => $nextContentNumber,
            'content' => $content->content
        ]);
    }}
