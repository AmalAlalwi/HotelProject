<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use GeneralTrait;

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),
            ['name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',]);

        if ($validator->fails()) {
            $code = $this->returnCodeAccordingToInput($validator);
            return $this->returnValidationError($code, $validator);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'email' => 'required|string|email|exists:users,email',
                'password' => 'required|string',
            ]);

        if ($validator->fails()) {
            $code = $this->returnCodeAccordingToInput($validator);
            return $this->returnValidationError($code, $validator);
        }

        if (!$token = JWTAuth::attempt($request->only('email', 'password'))) {
            return $this->returnError(401, 'The selected email or password is invalid.');

        }
        $user = $request->user();
        return response()->json(['token'=>$token,'name' => $user->name,
            'email' => $user->email,]);
    }
    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return $this->returnSuccess("Successfully logged out");
    }
    public function me(Request $request)
    {
             $user = JWTAuth::parseToken()->authenticate();
             return response()->json([
                'name' => $user->name,
                'email' => $user->email]);


    }
}
