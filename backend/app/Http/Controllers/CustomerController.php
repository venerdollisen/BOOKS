<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::where('user_id', auth()->id());

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $customers = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => $customers->items(),
            'meta' => [
                'total' => $customers->total(),
                'per_page' => $customers->perPage(),
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,null,id,user_id,' . auth()->id(),
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::create([
            'user_id' => auth()->id(),
            ...$validated
        ]);

        return response()->json($customer, 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        $this->authorize('view', $customer);
        return response()->json($customer);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id . ',id,user_id,' . auth()->id(),
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $customer->update($validated);

        return response()->json($customer);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->authorize('delete', $customer);
        $customer->delete();
        return response()->json(['message' => 'Customer deleted successfully']);
    }

    public function getActiveCustomers(): JsonResponse
    {
        $customers = Customer::where('user_id', auth()->id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json($customers);
    }
}
