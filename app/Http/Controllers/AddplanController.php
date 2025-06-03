<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Session;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class AddplanController extends Controller
{
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
    // التحقق من صحة البيانات
    $request->validate([
        'name' => 'required|string|max:255',
        'plan_id' => 'required|exists:plans,id',
        'file' => 'required|file|mimes:mp4,mp3,pdf,txt|max:10240',
        'type' => 'required|string|max:255',
        'task' => 'required|string|max:255',
        'practical' => 'required|string|max:255',
        'type_ai' => 'required|string|max:255',
    ]);

    $file = $request->file('file');
    $extension = strtolower($file->getClientOriginalExtension());

    // تحديد نوع المورد بناءً على الامتداد
    $resourceType = 'raw'; // الافتراضي
    if (in_array($extension, ['mp4', 'mp3'])) {
        $resourceType = 'video';
    }

    // رفع الملف إلى Cloudinary
    $uploadResult = Cloudinary::uploadFile(
        $file->getRealPath(),
        [
            'folder' => 'sessions/files',
            'upload_preset' => 'public_raw',
            'resource_type' => $resourceType,
            'access_mode' => 'public',
            'filename_override' => uniqid() . '.' . $extension
        ]
    );

    // إنشاء السجّل
    $session = Session::create([
        'name' => $request->name,
        'content' => $uploadResult->getSecurePath(),
        'plan_id' => $request->plan_id,
        'type' => $request->type,
        'task' => $request->task,
        'practical' => $request->practical,
        'type_ai' => $request->type_ai
    ]);

    // تعديل الرابط لتحميل ملفات معينة مباشرة (مثل PDF و TXT)
    $fileUrl = $uploadResult->getSecurePath();
    if (in_array($extension, ['pdf', 'txt'])) {
        $fileUrl .= '?fl_attachment';
    }

    return response()->json([
        'message' => 'تم إنشاء السيشن ورفع الملف بنجاح',
        'session' => $session,
    ], 201);
}

public function plans()
{
    $plans = Plan::select('id', 'level', 'name')
        ->with(['sessions' => function ($query) {
            $query->select('id', 'name', 'type_ai', 'plan_id');
        }])
        ->get()
        ->map(function ($plan) {
            return [
                'plan_id' => $plan->id,
                'name' => $plan->name,
                'Anxiety level' => $plan->level,
                'sessions' => $plan->sessions->map(function ($session) {
                    return [
                        'name' => $session->name,
                        'type' => $session->type_ai,
                    ];
                }),
            ];
        });

    return response()->json($plans);
}
public function updateSession(Request $requestd)
{
    $session = Session::findOrFail($requestd->id);

    $request->validate([
        'name' => 'sometimes|required|string|max:255',
        'task' => 'sometimes|required|string|max:255',
        'practical' => 'sometimes|required|string|max:255',
        'file' => 'sometimes|file|mimes:mp4,mp3,pdf,txt|max:10240',
    ]);

    if ($request->has('name')) {
        $session->name = $request->name;
    }

    if ($request->has('task')) {
        $session->task = $request->task;
    }

    if ($request->has('practical')) {
        $session->practical = $request->practical;
    }

    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        $resourceType = 'raw';
        if (in_array($extension, ['mp4', 'mp3'])) {
            $resourceType = 'video';
        }

        $uploadResult = Cloudinary::uploadFile(
            $file->getRealPath(),
            [
                'folder' => 'sessions/files',
                'upload_preset' => 'public_raw',
                'resource_type' => $resourceType,
                'access_mode' => 'public',
                'filename_override' => uniqid() . '.' . $extension
            ]
        );

        // 👇 تعديل الرابط بشكل صحيح قبل التخزين
        $fileUrl = $uploadResult->getSecurePath();
        if (in_array($extension, ['pdf', 'txt'])) {
            $fileUrl .= '?fl_attachment';
        }

        $session->content = $fileUrl; 
    }

    $session->save();

    return response()->json([
        'message' => 'تم تحديث بيانات السيشن بنجاح',
        'session' => $session,
    ]);
}



}

