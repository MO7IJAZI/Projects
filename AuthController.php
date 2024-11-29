<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{
    use GeneralTrait;

    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);
        $token = Auth::guard('api')->attempt($credentials);

        if (!$token)
            return $this->returnError(401,'token Not found');

        $user =Auth::guard('api')->user();
        $user->token = $token;
        $user->loadMissing(['roles']);

        return $this->returnData($user,'operation completed successfully');

    }


    public function register(RegisterRequest $request)
    {
        $user=User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => $request->password,
            'phone_number'   => $request->phone_number
        ]);
        $token = JWTAuth::fromUser($user);
        $user->token=$token;

        if (!$token)
            return $this->returnError(401,'Unauthorized');

        $role = Role::find($request->role_id);
        if(!$role)
            return $this->returnError(404,'Role Not found');
        $user->assignRole($role);
        $user->loadMissing(['roles']);

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

    public function getAll()
    {
        try {
            $data = User::all();
            if(count($data)>0)
               $data->loadMissing(['roles']);
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(),'Please try again later');
        }
    }

    public function getById($id)
    {
        try {
            $data=User::find($id);
            if (!$data) {
                return $this->returnError(404,'Not found');
            }
            $data->loadMissing(['roles']);
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(),'Please try again later');

        }
    }


}
