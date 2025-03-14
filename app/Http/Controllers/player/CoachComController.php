<?php

namespace App\Http\Controllers\player;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ComPre;
use Illuminate\Support\Facades\Auth;
use App\Models\Card;

class CoachComController extends Controller
{
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
    
        // تحويل التاريخ من "d-m-Y" إلى "Y-m-d"
        $formattedDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
    
        $card = Card::create([
            'name' => $request->name,
            'number' => $request->number,
            'date' => $formattedDate, // حفظ التاريخ بصيغة Y-m-d
            'cvv' => $request->cvv,
            'user_id' => Auth::id(),
        ]);
    
        return response()->json([
            'message' => 'Card saved successfully!',
            'data' => $card
        ], 201);
    }
    
    
}

