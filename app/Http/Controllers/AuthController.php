<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $request->user()->createToken('auth_token')->plainTextToken;
        $is_admin = $request->user()->is_admin;
        $name = $request->user()->name;
        // Cookie::queue('auth_token', $token, 60 * 24); // Cookie válida por 1 día

        return response()->json([
            'token' => $token,
            'is_admin' => $is_admin,
            'name' => $name
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'ok'], 200);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}