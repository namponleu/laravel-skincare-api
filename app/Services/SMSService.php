<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SMSService
{
    /**
     * Send SMS message
     * 
     * @param string $phoneNumber Phone number (with country code, e.g., +1234567890)
     * @param string $message Message to send
     * @return array Response from SMS service
     */
    public static function send($phoneNumber, $message)
    {
        // Option 1: Using a free SMS API (TextLocal, ClickSend, etc.)
        // You can configure which service to use via .env
        
        $service = config('services.sms.provider', 'textlocal');
        
        switch ($service) {
            case 'textlocal':
                return self::sendViaTextLocal($phoneNumber, $message);
            case 'clicksend':
                return self::sendViaClickSend($phoneNumber, $message);
            case 'vonage':
                return self::sendViaVonage($phoneNumber, $message);
            default:
                return self::sendViaTextLocal($phoneNumber, $message);
        }
    }

    /**
     * Send SMS via TextLocal (Free tier available)
     * Sign up at: https://www.textlocal.in/
     */
    private static function sendViaTextLocal($phoneNumber, $message)
    {
        $apiKey = config('services.sms.textlocal.api_key');
        $sender = config('services.sms.textlocal.sender', 'TXTLCL');
        
        if (!$apiKey) {
            Log::warning('TextLocal API key not configured. SMS not sent.', [
                'phone' => $phoneNumber
            ]);
            return ['error' => 'SMS service not configured'];
        }

        // Remove + from phone number for TextLocal
        $phoneNumber = str_replace('+', '', $phoneNumber);
        
        $response = Http::asForm()->post('https://api.textlocal.in/send/', [
            'apikey' => $apiKey,
            'numbers' => $phoneNumber,
            'message' => $message,
            'sender' => $sender,
        ]);

        $result = $response->json();
        
        if ($response->successful() && isset($result['status']) && $result['status'] === 'success') {
            Log::info('SMS sent successfully via TextLocal', [
                'phone' => $phoneNumber,
                'response' => $result
            ]);
            return ['success' => true, 'data' => $result];
        } else {
            Log::error('SMS sending failed via TextLocal', [
                'phone' => $phoneNumber,
                'response' => $result,
                'status' => $response->status()
            ]);
            return ['success' => false, 'error' => $result['errors'][0]['message'] ?? 'SMS sending failed'];
        }
    }

    /**
     * Send SMS via ClickSend (Free credits available)
     * Sign up at: https://www.clicksend.com/
     */
    private static function sendViaClickSend($phoneNumber, $message)
    {
        $username = config('services.sms.clicksend.username');
        $apiKey = config('services.sms.clicksend.api_key');
        
        if (!$username || !$apiKey) {
            Log::warning('ClickSend credentials not configured. SMS not sent.', [
                'phone' => $phoneNumber
            ]);
            return ['error' => 'SMS service not configured'];
        }

        $response = Http::withBasicAuth($username, $apiKey)
            ->post('https://rest.clicksend.com/v3/sms/send', [
                'messages' => [
                    [
                        'source' => 'laravel',
                        'body' => $message,
                        'to' => $phoneNumber,
                    ]
                ]
            ]);

        $result = $response->json();
        
        if ($response->successful() && isset($result['response_code']) && $result['response_code'] === 'SUCCESS') {
            Log::info('SMS sent successfully via ClickSend', [
                'phone' => $phoneNumber,
                'response' => $result
            ]);
            return ['success' => true, 'data' => $result];
        } else {
            Log::error('SMS sending failed via ClickSend', [
                'phone' => $phoneNumber,
                'response' => $result,
                'status' => $response->status()
            ]);
            return ['success' => false, 'error' => $result['response_msg'] ?? 'SMS sending failed'];
        }
    }

    /**
     * Send SMS via Vonage (Nexmo) - Free tier available
     * Sign up at: https://www.vonage.com/
     */
    private static function sendViaVonage($phoneNumber, $message)
    {
        $apiKey = config('services.sms.vonage.api_key');
        $apiSecret = config('services.sms.vonage.api_secret');
        $from = config('services.sms.vonage.from', 'Vonage');
        
        if (!$apiKey || !$apiSecret) {
            Log::warning('Vonage credentials not configured. SMS not sent.', [
                'phone' => $phoneNumber
            ]);
            return ['error' => 'SMS service not configured'];
        }

        $response = Http::asForm()->post('https://rest.nexmo.com/sms/json', [
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'to' => $phoneNumber,
            'from' => $from,
            'text' => $message,
        ]);

        $result = $response->json();
        
        if ($response->successful() && isset($result['messages'][0]['status']) && $result['messages'][0]['status'] === '0') {
            Log::info('SMS sent successfully via Vonage', [
                'phone' => $phoneNumber,
                'response' => $result
            ]);
            return ['success' => true, 'data' => $result];
        } else {
            Log::error('SMS sending failed via Vonage', [
                'phone' => $phoneNumber,
                'response' => $result,
                'status' => $response->status()
            ]);
            return ['success' => false, 'error' => $result['messages'][0]['error-text'] ?? 'SMS sending failed'];
        }
    }

    /**
     * Send OTP via SMS
     * 
     * @param string $phoneNumber Phone number
     * @param string $otpCode 6-digit OTP code
     * @return array Response from SMS service
     */
    public static function sendOtp($phoneNumber, $otpCode)
    {
        $message = "Your OTP code is: {$otpCode}. Valid for 10 minutes. Do not share this code with anyone.";
        
        return self::send($phoneNumber, $message);
    }
}
