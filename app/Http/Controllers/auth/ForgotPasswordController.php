<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendVerificationCode;
class ForgotPasswordController extends Controller
{
    public function sendemailcode(Request $request)
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
    
        $verificationCode = rand(100000, 999999);
    
        try {
            Mail::to("hamada@gmail.com")->send(new SendVerificationCode($verificationCode));
    
            return response()->json([
                'success' => true,
                'message' => 'تم إرسال كود التحقق إلى البريد الإلكتروني بنجاح.',
                'verification_code' => $verificationCode
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال البريد الإلكتروني.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function sendCode(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $email = $user->email;
        $code = $user->code;
        // $user->notify(new ResetPassword($code));
        return response()->json(['message' => 'Code sent successfully to your email.'], 200);
    }

    public function vertifyCode(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $code = $user->code;
        if ($user->code == $request->code) {
            return response()->json([
                'message' => ' successfully ',
            ], 200);
        }
        return response()->json(['message' => 'code is not correct'], 400);
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
        $user->password = Hash::make($request->password);
        $user->code = mt_rand(1000, 9999);
        $user->save();
        return response()->json(['message' => 'User updated successfully'], 200);    
}
}