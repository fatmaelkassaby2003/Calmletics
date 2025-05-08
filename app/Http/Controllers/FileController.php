<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Models\File;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mp3,pdf|max:10240',
            'rec1' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mp3,pdf|max:10240',
            'rec2' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mp3,pdf|max:10240',
            'rec3' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mp3,pdf|max:10240',
        ]);
    
        $fileModel = new File();
    
        if ($request->hasFile('file')) {
            $uploadedFileUrl = Cloudinary::upload(
                $request->file('file')->getRealPath(), 
                [
                    'resource_type' => 'raw', 
                    'type' => 'upload'
                ]
            )->getSecurePath();
        
            $fileModel->file = $uploadedFileUrl;
        }
    
        if ($request->hasFile('rec1')) {
            $uploadedRec1Url = Cloudinary::uploadFile(
                $request->file('rec1')->getRealPath(), 
                ['resource_type' => 'auto']
            )->getSecurePath();
            $fileModel->rec1 = $uploadedRec1Url;
        }
    
        if ($request->hasFile('rec2')) {
            $uploadedRec2Url = Cloudinary::uploadFile(
                $request->file('rec2')->getRealPath(), 
                ['resource_type' => 'auto']
            )->getSecurePath();
            $fileModel->rec2 = $uploadedRec2Url;
        }
    
        if ($request->hasFile('rec3')) {
            $uploadedRec3Url = Cloudinary::uploadFile(
                $request->file('rec3')->getRealPath(), 
                ['resource_type' => 'auto']
            )->getSecurePath();
            $fileModel->rec3 = $uploadedRec3Url;
        }
    
        $fileModel->save();
    
        return response()->json([
            'message' => 'تم رفع الملفات بنجاح',
            'data' => [
                'file' => $fileModel->file,
                'rec1' => $fileModel->rec1,
                'rec2' => $fileModel->rec2,
                'rec3' => $fileModel->rec3,
            ],
        ]);
    }


    public function getRec3()
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => 'يجب تسجيل الدخول أولاً',
            ], 401);
        }
    
        $file = File::whereNotNull('rec3')->latest()->first();
    
        if (!$file) {
            return response()->json([
                'message' => 'لا يوجد rec3 مسجل حتى الآن',
            ], 404);
        }
    
        return response()->json([
            'rec3' => $file->rec3,
        ]);
    }
    

    public function getRecording(Request $request)
{
    if (!auth()->check()) {
        return response()->json([
            'message' => 'يجب تسجيل الدخول أولاً',
        ], 401);
    }
    $request->validate([
        'file' => 'required|in:1,2'
    ]);

    $file = File::whereNotNull('rec1')->whereNotNull('rec2')->latest()->first();

    if (!$file) {
        return response()->json([
            'message' => 'لا يوجد rec1 أو rec2 متاح حالياً.',
        ], 404);
    }

    if ($request->input('file') == 1) {
        return response()->json([
            'rec1' => $file->rec1,
        ]);
    } elseif ($request->input('file') == 2) {
        return response()->json([
            'rec2' => $file->rec2,
        ]);
    }
}

    
}
