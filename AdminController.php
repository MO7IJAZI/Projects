<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller
{
    use GeneralTrait;

    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        $token = Auth::guard('api_admin')->attempt($credentials);

        if (!$token)
            return $this->returnError(401,'token Not found');

        $user =auth('api_admin')->user();
        $user->token = $token;

        return $this->returnData($user,'operation completed successfully');

    }


    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        if ($token) {
            try {
                JWTAuth::setToken($token)->invalidate();
                auth()->logout();
                return $this->returnSuccessMessage('Logged out successfully');
            } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                return $this->returnError(500,'some thing went wrongs');
            }
        } else {
            return $this->returnError(500,'some thing went wrongs');
        }
    }



}
