<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    /**
     * Get all settings for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $settings = Setting::where('user_id', auth()->id())->get();

        return response()->json([
            'data' => $settings->mapWithKeys(fn($s) => [$s->key => $s->value]),
        ]);
    }

    /**
     * Update AR and AP account settings.
     */
    public function updateGLAccounts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ar_account_id' => 'nullable|exists:accounts,id',
            'ap_account_id' => 'nullable|exists:accounts,id',
        ]);

        // Save the AR account setting
        if ($validated['ar_account_id']) {
            Setting::set('ar_account_id', $validated['ar_account_id']);
        }

        // Save the AP account setting
        if ($validated['ap_account_id']) {
            Setting::set('ap_account_id', $validated['ap_account_id']);
        }

        return response()->json([
            'message' => 'GL account settings updated successfully',
            'data' => [
                'ar_account_id' => Setting::get('ar_account_id'),
                'ap_account_id' => Setting::get('ap_account_id'),
            ],
        ]);
    }

    /**
     * Get GL account settings.
     */
    public function getGLAccounts(): JsonResponse
    {
        return response()->json([
            'data' => [
                'ar_account_id' => Setting::get('ar_account_id'),
                'ap_account_id' => Setting::get('ap_account_id'),
            ],
        ]);
    }
}
