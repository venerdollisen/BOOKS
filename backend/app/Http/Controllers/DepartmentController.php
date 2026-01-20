<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    /**
     * Get all departments with pagination and filtering
     */
    public function index(Request $request): JsonResponse
    {
        $query = Department::query();

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Pagination
        $perPage = min((int)$request->get('per_page', 20), 100);
        $data = $query->paginate($perPage);

        return response()->json($data);
    }

    /**
     * Get a single department
     */
    public function show(Department $department): JsonResponse
    {
        return response()->json($department->load('projects'));
    }

    /**
     * Create a new department
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|unique:departments,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_name' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $department = Department::create($validated);

        return response()->json([
            'message' => 'Department created successfully',
            'data' => $department,
        ], 201);
    }

    /**
     * Update a department
     */
    public function update(Request $request, Department $department): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|unique:departments,code,' . $department->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_name' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $department->update($validated);

        return response()->json([
            'message' => 'Department updated successfully',
            'data' => $department,
        ]);
    }

    /**
     * Delete a department
     */
    public function destroy(Department $department): JsonResponse
    {
        $department->delete();

        return response()->json([
            'message' => 'Department deleted successfully',
        ]);
    }
}
