<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FCMService
{
    /**
     * Send push notification to a single device
     */
    public static function send($deviceToken, $title, $body, $data = [])
    {
        $serverKey = config('services.fcm.server_key');

        if (!$serverKey) {
            Log::error('FCM Server Key not configured');
            return ['error' => 'FCM Server Key not configured'];
        }

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            "to" => $deviceToken,
            "notification" => [
                "title" => $title,
                "body"  => $body,
                "sound" => "default",
                "icon" => "ic_notification"
            ],
            "data" => $data, // optional custom payload
            "priority" => "high"
        ]);

        $result = $response->json();
        
        if ($response->successful()) {
            Log::info('FCM notification sent successfully', [
                'device_token' => $deviceToken,
                'title' => $title,
                'response' => $result
            ]);
        } else {
            Log::error('FCM notification failed', [
                'device_token' => $deviceToken,
                'title' => $title,
                'response' => $result,
                'status' => $response->status()
            ]);
        }

        return $result;
    }

    /**
     * Send push notification to multiple devices
     */
    public static function sendToMultiple($deviceTokens, $title, $body, $data = [])
    {
        $serverKey = config('services.fcm.server_key');

        if (!$serverKey) {
            Log::error('FCM Server Key not configured');
            return ['error' => 'FCM Server Key not configured'];
        }

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            "registration_ids" => $deviceTokens,
            "notification" => [
                "title" => $title,
                "body"  => $body,
                "sound" => "default",
                "icon" => "ic_notification"
            ],
            "data" => $data,
            "priority" => "high"
        ]);

        $result = $response->json();
        
        if ($response->successful()) {
            Log::info('FCM notification sent to multiple devices', [
                'device_count' => count($deviceTokens),
                'title' => $title,
                'response' => $result
            ]);
        } else {
            Log::error('FCM notification to multiple devices failed', [
                'device_count' => count($deviceTokens),
                'title' => $title,
                'response' => $result,
                'status' => $response->status()
            ]);
        }

        return $result;
    }

    /**
     * Send notification to all devices of a user
     */
    public static function sendToUser($userId, $title, $body, $data = [])
    {
        $devices = \App\Models\UserDevice::where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('device_token')
            ->toArray();

        if (empty($devices)) {
            Log::warning('No active devices found for user', ['user_id' => $userId]);
            return ['error' => 'No active devices found for user'];
        }

        return self::sendToMultiple($devices, $title, $body, $data);
    }
}
