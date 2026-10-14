<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
            'device_name' => ['sometimes', 'string', 'max:80'],
        ]);
        $user = User::where('email', strtolower(trim($data['email'])))->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'Email atau kata sandi tidak cocok.']);
        }
        $token = $user->createToken($data['device_name'] ?? 'vendor-api', ['vendor'], now()->addDay());

        return response()->json([
            'data' => $user->only(['id', 'name', 'email', 'role', 'facility']),
            'token' => $token->plainTextToken, 'token_type' => 'Bearer',
            'expires_at' => $token->accessToken->expires_at->toISOString(),
        ])->header('Cache-Control', 'no-store');
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->only(['id', 'name', 'email', 'role', 'facility'])]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
        if ($request->hasSession()) {
            auth('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(['message' => 'Kamu sudah keluar.']);
    }
}
