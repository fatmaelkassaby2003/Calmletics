<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

class CheckToken
{
    /**
     * تحقق من التوكن الصالح
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // الحصول على التوكن من الهيدر Authorization
        $token = $request->bearerToken();

        // التحقق من وجود التوكن
        if (!$token) {
            return response()->json(['error' => 'Unauthorized. Token not provided.'], 401);
        }

        try {
            // التحقق من صلاحية التوكن
            $user = JWTAuth::authenticate($token);  // إذا كان التوكن صالح سيتم استخراج المستخدم
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }

        // إضافة المستخدم إلى الطلب ليكون متاحًا في الـ Controller
        $request->user = $user;

        return $next($request); // متابعة التنفيذ إلى الخطوة التالية (الـ Controller)
    }
}
