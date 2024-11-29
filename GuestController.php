<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Guest;
use App\Models\Wallet;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class GuestController extends Controller
{
    use GeneralTrait;

    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        $token = Auth::guard('api_guest')->attempt($credentials);

        if (!$token)
            return $this->returnError(401,'token Not found');

        $user =auth('api_guest')->user();
        $user->token = $token;

        return $this->returnData($user,'operation completed successfully');

    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:guests',
            'password' => 'required|string|min:6',
            'national_number'=>'required|integer'
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError(422,$validator);
        }

        $user=Guest::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => $request->password,
            'national_number'=> $request->national_number,
        ]);

        $token = JWTAuth::fromUser($user);
        $user->token=$token;

        if (!$token)
            return $this->returnError(401,'Unauthorized');


        $user->wallet()->create([
            'number' => random_int(1000000000000, 9000000000000),
            'value' => 0,
        ]);


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
