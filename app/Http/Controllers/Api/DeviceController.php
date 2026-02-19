<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    /**
     * Save or update device token for authenticated user
     */
    public function saveDeviceToken(Request $request): JsonResponse
    {
        $request->validate([
            'device_token' => 'required|string',
            'device_type' => 'nullable|string|in:android,ios,web',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        $device = $user->devices()->updateOrCreate(
            ['device_token' => $request->device_token],
            [
                'device_type' => $request->device_type ?? 'unknown',
                'device_name' => $request->device_name,
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device token saved successfully',
            'device' => $device
        ]);
    }

    /**
     * Remove device token
     */
    public function removeDeviceToken(Request $request): JsonResponse
    {
        $request->validate([
            'device_token' => 'required|string',
        ]);

        $user = Auth::user();
        
        $deleted = $user->devices()
            ->where('device_token', $request->device_token)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Device token removed successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Device token not found'
        ], 404);
    }

    /**
     * Get user's devices
     */
    public function getUserDevices(): JsonResponse
    {
        $user = Auth::user();
        $devices = $user->devices()->where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'devices' => $devices
        ]);
    }

    /**
     * Deactivate device token
     */
    public function deactivateDeviceToken(Request $request): JsonResponse
    {
        $request->validate([
            'device_token' => 'required|string',
        ]);

        $user = Auth::user();
        
        $updated = $user->devices()
            ->where('device_token', $request->device_token)
            ->update(['is_active' => false]);

        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'Device token deactivated successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Device token not found'
        ], 404);
    }
}
