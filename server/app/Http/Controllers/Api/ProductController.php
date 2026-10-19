<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(
            max((int) $request->query('per_page', 10), 1),
            100
        );

        $products = Product::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->query('search');

                $query->where(function ($q) use ($search) {
                    $q->where('sku', 'ilike', "%{$search}%")
                        ->orWhere('name', 'ilike', "%{$search}%")
                        ->orWhere('category', 'ilike', "%{$search}%");
                });
            })
            ->when(
                $request->query('status') === 'active',
                fn($query) => $query->where('is_active', true)
            )
            ->when(
                $request->query('status') === 'inactive',
                fn($query) => $query->where('is_active', false)
            )
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully.',
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'min_temperature' => ['required', 'numeric', 'between:-100,100'],
            'max_temperature' => [
                'required',
                'numeric',
                'between:-100,100',
                'gt:min_temperature',
            ],
            'max_excursion_seconds' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'temperature_protocol' => ['nullable', 'string', 'max:5000'],
            'protocol_reference' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (
            isset($validated['max_excursion_seconds']) &&
            $validated['max_excursion_seconds'] === 0
        ) {
            throw ValidationException::withMessages([
                'max_excursion_seconds' =>
                ['Durasi harus lebih besar dari nol atau null jika belum ditetapkan.'],
            ]);
        }

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => $product,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully.',
            'data' => $product,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'sku' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($product->id),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'min_temperature' => [
                'sometimes',
                'required',
                'numeric',
                'between:-100,100',
            ],
            'max_temperature' => [
                'sometimes',
                'required',
                'numeric',
                'between:-100,100',
            ],
            'max_excursion_seconds' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
            ],
            'temperature_protocol' => [
                'sometimes',
                'nullable',
                'string',
                'max:5000',
            ],
            'protocol_reference' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $min = $validated['min_temperature'] ?? $product->min_temperature;
        $max = $validated['max_temperature'] ?? $product->max_temperature;

        if ((float) $max <= (float) $min) {
            throw ValidationException::withMessages([
                'max_temperature' => [
                    'Suhu maksimum harus lebih besar dari suhu minimum.',
                ],
            ]);
        }

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => $product->fresh(),
        ]);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $product = Product::findOrFail($id);
        $product->update([
            'is_active' => $validated['is_active'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product status updated successfully.',
            'data' => $product->fresh(),
        ]);
    }
}