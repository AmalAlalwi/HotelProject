<?php
namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtAuthenticate
{
public function handle($request, Closure $next)
{

    try {
        // التحقق من صحة الـ token
        $user = JWTAuth::parseToken()->authenticate();

// إذا لم يتم العثور على المستخدم
        if (!$user) {
            return response()->json([
                'status' => false,
                'errNum' => 404,
                'msg' => 'User not found'
                ]);
        }

    } catch (TokenExpiredException $e) {
// إذا انتهت صلاحية الـ token
        return response()->json([
            'status' => false,
            'errNum' => 401,
            'msg' => 'Token has expired '
        ]);

    } catch (TokenInvalidException $e) {
// إذا كان الـ token غير صالح
        return response()->json([
            'status' => false,
            'errNum' => 401,
            'msg' => 'Token has invalid']);

    } catch (JWTException $e) {
// إذا كان هناك خطأ عام في الـ token
        return response()->json([
            'status' => false,
            'errNum' => 401,
            'msg' => 'Unauthorized']);
    }

// إذا كان الـ token صالحًا، انتقل إلى الخطوة التالية
    return $next($request);
}
}

