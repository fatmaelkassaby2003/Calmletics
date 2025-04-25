<?php

namespace App\Http\Controllers\auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendVerificationCode;
use Illuminate\Support\Facades\Auth;

class ForgotPasswordController extends Controller
{
    public function sendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'البريد الإلكتروني غير صحيح.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $verificationCode = rand(1000, 9999);
        $user->code = $verificationCode;
        $user->save();

        try {
            Mail::to($user->email)->send(new SendVerificationCode($verificationCode));

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال كود التحقق إلى البريد الإلكتروني بنجاح.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال البريد الإلكتروني.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function vertifyCode(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'code' => 'required|string', 
    ]);

    $user = User::where('email', $request->email)->first();
    if (!$user) {
        return response()->json(['message' => 'المستخدم غير موجود'], 404);
    }

    $inputCode = trim($request->code);

    if ((string)$user->code !== (string)$inputCode) {
        return response()->json(['message' => 'الكود غير صحيح'], 400);
    }

    return response()->json(['message' => 'تم التحقق بنجاح'], 200);
}


    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'الالمستخدم غير موجود'], 400);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'تم تحديث كلمة المرور بنجاح'], 200);
    }
}
