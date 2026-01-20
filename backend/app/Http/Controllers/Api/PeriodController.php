<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Period;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $periods = Period::orderBy('start_date', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $periods,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:periods',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
        ]);

        $period = Period::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Period created successfully',
            'data' => $period,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $period = Period::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $period,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $period = Period::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|unique:periods,name,' . $id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
        ]);

        $period->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Period updated successfully',
            'data' => $period,
        ]);
    }

    /**
     * Close a period (no more transactions allowed)
     */
    public function close(string $id)
    {
        $period = Period::findOrFail($id);
        
        if ($period->status !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'Only open periods can be closed',
            ], 422);
        }

        $period->close();

        return response()->json([
            'success' => true,
            'message' => 'Period closed successfully',
            'data' => $period,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $period = Period::findOrFail($id);
        
        // Prevent deletion of closed periods
        if ($period->status !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete closed periods',
            ], 422);
        }

        $period->delete();

        return response()->json([
            'success' => true,
            'message' => 'Period deleted successfully',
        ]);
    }
}
