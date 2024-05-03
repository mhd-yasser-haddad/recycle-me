<?php

namespace App\Http\Controllers\API;

use App\Models\User;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;

use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserAuthController extends Controller
{
    use ResponseTrait;

    public function register(RegisterRequest $request)
    {
        $data = $request->all();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        return $this->customResponse([], __('api.user_created'), 200);
    }

    public function login(LoginRequest $request)
    {

        $data = $request->all();
        $user = User::where('email', $data['email'])->first();
        if (!$user || !password_verify($data['password'], $user->password)) {
            return $this->customResponse([], __('api.invalid_credentials'), 401);
        }
        $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;
        $data = ['user' => $user, 'token' => $token];
        return $this->customResponse($data, __('api.success'), 200);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json([
            "message" => "logged out"
        ]);
    }

    public function getUser()
    {
        return $this->customResponse(['user' => auth()->user()], __('api.success'), 200);
    }
}
