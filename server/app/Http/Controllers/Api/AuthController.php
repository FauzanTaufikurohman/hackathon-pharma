<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (
            ! $user
            || ! Hash::check($validated['password'], $user->password)
            || ! $user->is_active
        ) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah, atau akun tidak aktif.'],
            ]);
        }

        return $this->tokenResponse($user);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'company_name' => ['required', 'string', 'max:255'],
            'company_type' => ['required', 'in:manufacturer,distributor,pharmacy'],
            'whatsapp_number' => ['nullable', 'string', 'max:25'],
            'whatsapp_opt_in' => ['sometimes', 'boolean'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $customer = Customer::create([
                'company_name' => $validated['company_name'],
                'contact_name' => $validated['name'],
                'email' => $validated['email'],
                'company_type' => $validated['company_type'],
                'whatsapp_number' => $validated['whatsapp_number'] ?? null,
                'whatsapp_opt_in' => $validated['whatsapp_opt_in'] ?? false,
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            $user->forceFill([
                'role' => 'customer',
                'customer_id' => $customer->id,
            ])->save();

            return $user;
        });

        return $this->tokenResponse($user, 201, 'Registrasi berhasil.');
    }

    private function tokenResponse(User $user, int $status = 200, string $message = 'Login berhasil.'): JsonResponse
    {
        $token = $user->createToken('anteraja-pharma')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'customer_id' => $user->customer_id,
                ],
            ],
        ], $status);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }
}
