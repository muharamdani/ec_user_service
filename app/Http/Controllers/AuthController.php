<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Services\AuthServices;

class AuthController extends Controller
{
    protected $authService;
    public function __construct(AuthServices $authService)
    {
        $this->authService = $authService;
        $this->middleware('auth:api', ['except' => [
            'login',
            'register',
            'refresh'
            ]
        ]);
    }

    public function login(LoginRequest $request)
    {
        $request->validated();

        return $this->authService->login($request);
    }

    public function register(RegisterRequest $request) {
        $request->validated();

        return $this->authService->register($request);
    }

    public function refresh()
    {
        return $this->authService->refresh();
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function profile()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
        ]);
    }
}
