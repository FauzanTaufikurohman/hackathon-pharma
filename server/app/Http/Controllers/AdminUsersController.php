<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\User;
use App\OperationsWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminUsersController extends Controller
{
    public function index(Request $request, OperationsWorkflow $workflow): JsonResponse
    {
        $workflow->authorize($request->user(), 'admin');
        $data = $request->validate(['page' => ['sometimes', 'integer', 'between:1,100000'],
            'search' => ['sometimes', 'string', 'max:120']]);
        $query = User::query()->select(['id', 'name', 'email', 'role', 'is_active', 'facility', 'courier_profile', 'updated_at']);
        if (trim($data['search'] ?? '') !== '') {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower(trim($data['search']))).'%';
            $query->where(fn ($q) => $q->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [$pattern])
                ->orWhereRaw("LOWER(email) LIKE ? ESCAPE '!'", [$pattern]));
        }

        return response()->json($query->orderBy('id')->paginate(30))->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request, OperationsWorkflow $workflow): JsonResponse
    {
        $workflow->authorize($request->user(), 'admin');
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate(['name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:12', 'max:255'],
            'role' => ['required', Rule::in(['admin', 'checker', 'courier', 'buyer', 'vendor'])],
            'capacity_kg' => ['required_if:role,courier', 'nullable', 'numeric', 'gt:0', 'max:100000']]);
        $user = DB::transaction(function () use ($data, $request) {
            $actor = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            abort_unless($actor->role === 'admin' && $actor->is_active, 403);
            $fields = collect($data)->except('capacity_kg')->all();
            if ($data['role'] === 'courier') {
                $fields['courier_profile'] = ['capacity_kg' => (float) $data['capacity_kg']];
            }

            return User::create($fields + ['is_active' => true]);
        });
        Log::info('admin.user.created', ['actor_id' => $request->user()->id, 'user_id' => $user->id, 'role' => $user->role]);

        return response()->json(['data' => $user->only(['id', 'name', 'email', 'role', 'is_active'])], 201)
            ->header('Cache-Control', 'private, no-store');
    }

    public function update(Request $request, int $user, OperationsWorkflow $workflow): JsonResponse
    {
        $workflow->authorize($request->user(), 'admin');
        $roles = ['admin', 'checker', 'courier', 'buyer', 'vendor'];
        $data = $request->validate(['role' => ['required', Rule::in($roles)], 'is_active' => ['required', 'boolean'],
            'expected_role' => ['required', Rule::in($roles)], 'expected_active' => ['required', 'boolean'],
            'capacity_kg' => ['sometimes', 'nullable', 'numeric', 'gt:0', 'max:100000']]);
        $record = DB::transaction(function () use ($data, $request, $user) {
            $actor = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            abort_unless($actor->role === 'admin' && $actor->is_active, 403);
            $target = User::whereKey($user)->lockForUpdate()->firstOrFail();
            abort_unless($target->role === $data['expected_role'] && $target->is_active === (bool) $data['expected_active'],
                409, 'Data akun sudah berubah. Muat ulang daftar pengguna.');
            $accessChanged = $target->role !== $data['role'] || $target->is_active !== (bool) $data['is_active'];
            abort_if($target->id === $actor->id && $accessChanged, 422, 'Jangan mengubah peran atau menonaktifkan akun Anda sendiri.');
            if ($accessChanged) {
                $activeShipments = Shipment::whereNotIn('status', ['completed', 'delivered', 'returned', 'cancelled'])
                    ->where(fn ($q) => $q->where('buyer_id', $target->id)->orWhere('inspector_id', $target->id)->orWhere('courier_id', $target->id))->exists();
                abort_if($activeShipments, 409, 'Akun masih memiliki kiriman aktif. Selesaikan atau alihkan penugasan dahulu.');
            }
            $capacity = $data['capacity_kg'] ?? ($target->courier_profile['capacity_kg'] ?? null);
            if ($data['role'] === 'courier') {
                abort_unless($capacity > 0, 422, 'Isi kapasitas kurir sebelum mengaktifkan peran kurir.');
                $reserved = Shipment::where('courier_id', $target->id)->whereNotIn('status', ['completed', 'delivered', 'returned', 'cancelled'])->sum('weight_kg');
                abort_if($capacity < $reserved, 409, 'Kapasitas tidak boleh lebih kecil dari muatan yang telah ditugaskan.');
                $target->courier_profile = array_replace($target->courier_profile ?? [], ['capacity_kg' => (float) $capacity]);
            }
            $target->role = $data['role'];
            $target->is_active = (bool) $data['is_active'];
            $target->save();
            if ($accessChanged) {
                $target->tokens()->delete();
            }

            return $target;
        });
        Log::info('admin.user.access_updated', ['actor_id' => $request->user()->id, 'user_id' => $record->id,
            'role' => $record->role, 'is_active' => $record->is_active]);

        return response()->json(['data' => $record->only(['id', 'name', 'email', 'role', 'is_active'])])
            ->header('Cache-Control', 'private, no-store');
    }
}
