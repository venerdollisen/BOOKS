<?php

namespace App\Http\Controllers;

use App\Models\SubsidiaryAccount;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubsidiaryAccountController extends Controller
{
    /**
     * Get all subsidiary accounts with pagination and filtering
     */
    public function index(Request $request): JsonResponse
    {
        $query = SubsidiaryAccount::query();

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        }

        // Filter by account
        if ($request->has('account_id') && $request->account_id) {
            $query->where('account_id', $request->account_id);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Pagination
        $perPage = min((int)$request->get('per_page', 20), 100);
        $data = $query->with('account')->paginate($perPage);

        return response()->json($data);
    }

    /**
     * Get a single subsidiary account
     */
    public function show(SubsidiaryAccount $subsidiaryAccount): JsonResponse
    {
        return response()->json($subsidiaryAccount->load(['account', 'transactionItems']));
    }

    /**
     * Create a new subsidiary account
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'code' => 'required|unique:subsidiary_accounts,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:cost_center,profit_center,branch,division,custom',
            'status' => 'required|in:active,inactive',
        ]);

        $account = SubsidiaryAccount::create($validated);

        return response()->json([
            'message' => 'Subsidiary account created successfully',
            'data' => $account,
        ], 201);
    }

    /**
     * Update a subsidiary account
     */
    public function update(Request $request, SubsidiaryAccount $subsidiaryAccount): JsonResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'code' => 'required|unique:subsidiary_accounts,code,' . $subsidiaryAccount->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:cost_center,profit_center,branch,division,custom',
            'status' => 'required|in:active,inactive',
        ]);

        $subsidiaryAccount->update($validated);

        return response()->json([
            'message' => 'Subsidiary account updated successfully',
            'data' => $subsidiaryAccount,
        ]);
    }

    /**
     * Delete a subsidiary account
     */
    public function destroy(SubsidiaryAccount $subsidiaryAccount): JsonResponse
    {
        $subsidiaryAccount->delete();

        return response()->json([
            'message' => 'Subsidiary account deleted successfully',
        ]);
    }
}
