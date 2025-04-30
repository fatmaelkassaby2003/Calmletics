<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Session;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class AddplanController extends Controller
{
    // middleware لحماية الدالة
    public function storePlan(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'level' => 'required|string|max:100',
    ]);

    // تحقق من التكرار
    $exists = Plan::where('name', $request->name)
                  ->where('level', $request->level)
                  ->exists();

    if ($exists) {
        return response()->json([
            'message' => 'هذا البلان موجود بالفعل بنفس المستوى',
        ], 422); 
    }

    $plan = Plan::create([
        'name' => $request->name,
        'level' => $request->level,
    ]);

    return response()->json([
        'message' => 'تم إنشاء البلان بنجاح',
        'plan' => $plan
    ]);
}

    
    public function storeSession(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'plan_id' => 'required|exists:plans,id',
        'file' => 'required|file|mimes:mp4,mp3,pdf|max:10240',
    ]);

    $file = $request->file('file');

    // رفع الملف على Cloudinary
    $uploadedFileUrl = Cloudinary::uploadFile($file->getRealPath(), [
        'resource_type' => 'auto'
    ])->getSecurePath();

    $session = Session::create([
        'name' => $request->name,
        'content' => $uploadedFileUrl,
        'plan_id' => $request->plan_id,
    ]);

    return response()->json([
        'message' => 'تم إنشاء السيشن وربطها بالبلان بنجاح',
        'session' => $session,
    ]);
}

    
}

