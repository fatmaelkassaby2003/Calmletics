<?php

namespace App\Http\Controllers\coach;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ComPre;
use Illuminate\Support\Facades\Auth;
use App\Models\Card;
use App\Models\Plan;

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
    
        $plans = Plan::where('level', $request->level)->get();
    
        $plansWithContentStats = $plans->map(function ($plan) {
            $videoCount = 0;
            $audioCount = 0;
            $pdfCount = 0;
            $nonNullCount = 0;
            $videoImage = asset('front/images/vidio-icon.png'); 
            $audioImage = asset('front/images/audio-icon.png');
            $pdfImage = asset('front/images/subtitle.png');
    
            for ($i = 1; $i <= 24; $i++) {
                $field = 'content' . $i;
                $value = $plan->$field;
    
                if (!is_null($value)) {
                    $nonNullCount++;
    
                    $lower = strtolower($value);
    
                    if (preg_match('/\.(mp4|mov|avi|mkv)|youtube\.com|vimeo\.com/', $lower)) {
                        $videoCount++;
                        $videoImage = asset('front/images/vidio-icon.png');
                    } elseif (preg_match('/\.(mp3|wav|ogg)/', $lower)) {
                        $audioCount++;
                        $audioImage = asset('front/images/audio-icon.png'); 
                    } elseif (preg_match('/\.pdf/', $lower)) {
                        $pdfCount++;
                        $pdfImage = asset('front/images/subtitle.png');
                    }
                }
            }
                $videoPercentage = $nonNullCount ? round(($videoCount / $nonNullCount) * 100) : 0;
            $audioPercentage = $nonNullCount ? round(($audioCount / $nonNullCount) * 100) : 0;
            $pdfPercentage = $nonNullCount ? round(($pdfCount / $nonNullCount) * 100) : 0;
    
            return [
                'name' => $plan->name,
                'video_image' => $videoImage, 
                'audio_image' => $audioImage,
                'pdf_image' => $pdfImage, 
                'Sessions' => $nonNullCount,
                'video_percentage' => $videoPercentage, 
                'audio_percentage' => $audioPercentage,  
                'pdf_percentage' => $pdfPercentage, 
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

        return response()->json([
            'message' => 'Compre created successfully!',
            'data' => $compre
        ], 201);
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
    
    
}

