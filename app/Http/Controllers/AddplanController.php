<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class AddplanController extends Controller
{
    // middleware لحماية الدالة
    public function uploadToPlan(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'يجب تسجيل الدخول'], 401);
        }
    
        // التحقق من البيانات المطلوبة
        $request->validate([
            'column' => 'required|string',
            'name' => 'required|string|max:255',
            'file' => 'required|file|mimes:jpg,jpeg,png,mp4,mp3,pdf|max:10240',
        ]);
    
        $allowedColumns = [
            'content1', 'content2', 'content3', 'content4',
            'content5', 'content6', 'content7', 'content8',
            'content9', 'content10', 'content11', 'content12',
            'content13', 'content14', 'content15', 'content16',
        ];
    
        $columnName = $request->input('column');
    
        if (!in_array($columnName, $allowedColumns)) {
            return response()->json(['error' => 'اسم العمود غير صحيح'], 400);
        }
    
        $file = $request->file('file');
    
        // رفع الملف على Cloudinary
        $uploadedFileUrl = Cloudinary::uploadFile($file->getRealPath(), [
            'resource_type' => 'auto'
        ])->getSecurePath();
    
        // البحث عن الخطة أو إنشاؤها لو مش موجودة
        $plan = Plan::firstOrCreate(
            ['name' => $request->input('name')],
            []
        );
    
        // تحديث العمود
        $plan->$columnName = $uploadedFileUrl;
        $plan->save();
    
        // تجميع الأعمدة اللي مش فاضية
        $nonEmptyColumns = [];
        foreach ($allowedColumns as $col) {
            if (!empty($plan->$col)) {
                $nonEmptyColumns[$col] = $plan->$col;
            }
        }
    
        return response()->json([
            'message' => 'تم رفع الملف وتحديث الخطة بنجاح',
            $plan->name => $nonEmptyColumns,
        ]);
    }
    
    
}

