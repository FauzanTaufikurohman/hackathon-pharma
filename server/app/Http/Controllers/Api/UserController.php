<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function customers(Request $request): JsonResponse
    {
        $users = User::query()
            ->with('customer')
            ->where('role', 'customer')
            ->when(
                $request->boolean('active'),
                fn ($query) => $query->where('is_active', true)
            )
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%")
                        ->orWhereHas('customer', fn ($customer) => $customer
                            ->where('company_name', 'ilike', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'customer_id', 'is_active']);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function assignable(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:checker,courier'],
        ]);

        $users = User::query()
            ->where('role', $validated['role'])
            ->where('is_active', true)
            ->when(
                $validated['role'] === 'courier',
                fn ($query) => $query->whereDoesntHave(
                    'courierShipments',
                    fn ($shipments) => $shipments->whereIn(
                        'status',
                        ['checker_assigned', 'in_transit']
                    )
                )
            )
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }
}
