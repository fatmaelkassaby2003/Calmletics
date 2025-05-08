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
        'file' => 'required|file|mimes:mp4,mp3,pdf|max:10240',
        'type' => 'required|string|max:255',
        'task' => 'required|string|max:255',
        'practical' => 'required|string|max:255',
    ]);

    $file = $request->file('file');
    $extension = strtolower($file->getClientOriginalExtension()); // التأكد من أن الامتداد بحروف صغيرة

    // تحديد نوع المورد بناءً على امتداد الملف
    $resourceType = 'raw'; // الافتراضي لملفات PDF
    if (in_array($extension, ['mp4', 'mp3'])) {
        $resourceType = 'video'; // لملفات الفيديو والصوت
    }

    // رفع الملف إلى Cloudinary مع الإعدادات الصحيحة
    $uploadResult = Cloudinary::uploadFile(
        $file->getRealPath(),
        [
            'folder' => 'sessions/files',
            'upload_preset' => 'public_raw',
            'resource_type' => $resourceType,
            'access_mode' => 'public', // للتأكد من أن الملف قابل للوصول
            'filename_override' => uniqid() . '.' . $extension // تجنب مشاكل الأسماء المكررة
        ]
    );

    // إنشاء السيشين في قاعدة البيانات
    $session = Session::create([
        'name' => $request->name,
        'content' => $uploadResult->getSecurePath(),
        'plan_id' => $request->plan_id,
        'type' => $request->type,
        'task' => $request->task,
        'practical' => $request->practical
    ]);

    // إضافة معلمة fl_attachment لملفات PDF لضمان تحميلها بدلاً من معاينتها
    $fileUrl = $uploadResult->getSecurePath();
    if ($extension === 'pdf') {
        $fileUrl .= '?fl_attachment';
    }

    return response()->json([
        'message' => 'تم إنشاء السيشن ورفع الملف بنجاح',
        'session' => $session,
        'file_url' => $fileUrl
    ], 201);
}

}

