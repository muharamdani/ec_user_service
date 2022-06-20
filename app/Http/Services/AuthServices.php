<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Traits\RequestService;

class AuthServices
{
    use RequestService;

    protected $storeUri;
    public function __construct()
    {
        $this->storeUri = env('STORE_SERVICE_URI');
    }

    public function login($request)
    {
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => $this->respondWithToken($token),
        ]);
    }

    public function register($request) {
        // User create
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Store create
        $userData = [
            'user_id' => $user->id,
            'name' => $user->name,
        ];
        $store = $this->request('POST', $this->storeUri, '/', $userData);
        $store = json_decode($store);
        $token = Auth::login($user);
        $user->store = $store;

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => $this->respondWithToken($token),
        ]);
    }

    public function profile()
    {
        $user = Auth::user();
        $user_id = $user->id;
        $store = $this->request('GET', $this->storeUri, '/user/'.$user_id);
        $store = json_decode($store);
        $user->store = $store;

        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'authorisation' => $this->respondWithToken(Auth::refresh()),
        ]);
    }

    private function respondWithToken($token) {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ];
    }
}
