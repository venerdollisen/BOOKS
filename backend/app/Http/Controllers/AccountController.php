<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AccountController extends Controller
{
    /**
     * Get all accounts with server-side pagination, filtering, and sorting.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Account::query();

        // Search by code or name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter by account type
        if ($request->has('account_type') && $request->account_type) {
            $query->where('account_type', $request->account_type);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'account_type');
        $sortOrder = $request->input('sort_order', 'asc');
        
        if (in_array($sortBy, ['code', 'name', 'account_type', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('account_type', 'asc')->orderBy('code', 'asc');
        }

        // Pagination
        $perPage = min($request->input('per_page', 15), 100); // Max 100 per page
        $page = max($request->input('page', 1), 1);
        
        // Get total count before pagination
        $total = $query->count();
        
        // Paginate and include relationships
        $accounts = $query
            ->with(['parent'])
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $accounts->items(),
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, $total),
            ],
        ]);
    }

    /**
     * Store a newly created account.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:accounts',
            'name' => 'required|string|max:255',
            'account_type' => 'required|in:Asset,Liability,Equity,Income,Expense',
            'parent_id' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $account = Account::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'data' => $account,
        ], 201);
    }

    /**
     * Get a single account with relationships.
     */
    public function show(Account $account): JsonResponse
    {
        $account->load(['parent', 'children']);

        return response()->json([
            'success' => true,
            'data' => $account,
        ]);
    }

    /**
     * Update an account.
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|unique:accounts,code,' . $account->id,
            'name' => 'sometimes|string|max:255',
            'account_type' => 'sometimes|in:Asset,Liability,Equity,Income,Expense',
            'parent_id' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $account->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Account updated successfully',
            'data' => $account,
        ]);
    }

    /**
     * Delete an account.
     */
    public function destroy(Account $account): JsonResponse
    {
        try {
            $account->delete();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete account with sub-accounts or transactions',
            ], 409);
        }
    }

    /**
     * Get hierarchical chart of accounts.
     */
    public function hierarchy(): JsonResponse
    {
        $accounts = Account::whereNull('parent_id')
            ->with('children')
            ->where('is_active', true)
            ->orderBy('account_type')
            ->orderBy('code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $accounts,
        ]);
    }
}
