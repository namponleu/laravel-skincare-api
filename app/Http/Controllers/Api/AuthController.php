<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Message;
use App\Services\SMSService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    /**
     * Register user - supports both normal signup and phone OTP signup
     * 
     * Normal signup: Name + Phone + Password
     * Phone signup: Phone + Password + OTP
     */
    public function register(Request $request)
    {
        // Normalize terms field (accept both terms_accepted and termsAccepted from frontend)
        if ($request->has('termsAccepted') && !$request->has('terms_accepted')) {
            $request->merge(['terms_accepted' => $request->termsAccepted]);
        }

        // Check if this is phone OTP signup
        $isPhoneOtpSignup = $request->has('otp') && $request->has('session_id');

        if ($isPhoneOtpSignup) {
            // Sigup with phone number validation
            // Phone OTP Signup Validation (tel uniqueness checked on DB format e.g. 967772715)
            $validator = Validator::make($request->all(), [
                'tel' => [
                    'required',
                    'string',
                    'max:20',
                    function ($attr, $value, $fail) {
                        $telDb = $this->telForDatabase($value);
                        if (User::where('tel', $telDb)->exists()) {
                            $fail('The phone number has already been registered.');
                        }
                    },
                ],
                'password' => 'required|string|min:6',
                'otp' => 'required|string|size:6',
                'session_id' => 'required|string',
                'terms_accepted' => 'required|accepted',
                'user_type' => 'nullable|string|in:customer,admin,staff',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Database stores local number only (e.g. 967772715), no +855
            $telForDb = $this->telForDatabase($request->tel);
            
            // Verify OTP before registration (accept +855 or local format)
            $otpVerified = $this->verifyOtpForRegistration($request->tel, $request->otp, $request->session_id);
            
            if (!$otpVerified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP code or session expired',
                    'errors' => [
                        'otp' => ['The OTP code is invalid or has expired']
                    ]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Create user (phone OTP signup) - DB stores local number only, e.g. 967772715
            $userData = [
                'username' => $telForDb, // Login with 967772715
                'password' => Hash::make($request->password),
                'tel' => $telForDb, // DB: 967772715 only
                'name' => null,
                'email' => null,
                'user_type' => $request->user_type ?? 'customer',
                'is_active' => true,
                'registration_method' => 'phone_otp', // Track registration method
            ];

        } else {
            // Normal Signup Validation (tel uniqueness checked on DB format e.g. 967772715)
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:255|unique:users',
                'name' => 'required|string|max:255',
                'tel' => [
                    'nullable',
                    'string',
                    'max:20',
                    function ($attr, $value, $fail) {
                        if ($value === '' || $value === null) {
                            return;
                        }
                        $telDb = $this->telForDatabase($value);
                        if (User::where('tel', $telDb)->exists()) {
                            $fail('The phone number has already been registered.');
                        }
                    },
                ],
                'password' => 'required|string|min:6',
                'terms_accepted' => 'required|accepted',
                'user_type' => 'nullable|string|in:customer,admin,staff',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Create user (normal signup) - DB stores tel as local number only (e.g. 967772715)
            $userData = [
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'tel' => $request->tel ? $this->telForDatabase($request->tel) : null,
                'name' => $request->name,
                'email' => null,
                'user_type' => $request->user_type ?? 'customer',
                'is_active' => true,
                'registration_method' => 'normal', // Track registration method
            ];
        }

        // Create user
        $user = User::create($userData);

        // Send welcome message to the new user
        $this->sendWelcomeMessage($user);

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'tel' => $user->tel,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'registration_method' => $user->registration_method, // Include registration method
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]
        ], Response::HTTP_CREATED);
    }

    /**
     * Send OTP to phone number
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tel' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $tel = trim($request->tel);
        $telForDb = $this->telForDatabase($tel); // Local number for cache and DB (e.g. 967772715)
        
        // Generate 6-digit OTP
        $otp = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Generate unique session ID
        $sessionId = uniqid('otp_', true);
        
        // Store OTP in cache using local number only (so +855 or 967772715 both work)
        $cacheKey = "otp_{$telForDb}_{$sessionId}";
        Cache::put($cacheKey, $otp, now()->addMinutes(10));
        
        // Send OTP via SMS only - use +855 for SMS (e.g. +855967772715)
        $smsSent = false;
        $smsError = null;
        $telForSms = $this->telForSms($tel);
        
        try {
            $smsResult = SMSService::sendOtp($telForSms, $otp);
            
            if (isset($smsResult['success']) && $smsResult['success']) {
                $smsSent = true;
                Log::info('OTP sent successfully via SMS', [
                    'tel' => $telForDb,
                    'session_id' => $sessionId
                ]);
            } else {
                $smsError = $smsResult['error'] ?? 'Unknown error';
                Log::warning('Failed to send OTP via SMS', [
                    'tel' => $telForDb,
                    'error' => $smsError,
                    'otp' => $otp,
                    'session_id' => $sessionId
                ]);
            }
        } catch (\Exception $e) {
            $smsError = $e->getMessage();
            Log::error('Exception while sending OTP via SMS', [
                'tel' => $telForDb,
                'error' => $smsError,
                'otp' => $otp,
                'session_id' => $sessionId
            ]);
        }
        
        if (config('app.debug')) {
            Log::info("OTP generated for {$telForDb}: {$otp} (Session: {$sessionId}, SMS Sent: " . ($smsSent ? 'Yes' : 'No') . ")");
        }
        
        // Determine response message
        $message = $smsSent 
            ? 'OTP sent successfully via SMS' 
            : 'OTP generated successfully. SMS service not configured - use OTP from response for testing.';
        
        // Return OTP in response if:
        // 1. In development mode (APP_DEBUG=true), OR
        // 2. SMS failed to send (so user can still test)
        $returnOtp = config('app.debug') || !$smsSent;
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'session_id' => $sessionId,
                'expires_in' => 600, // seconds
                'sms_sent' => $smsSent,
                'otp' => $returnOtp ? $otp : null,
                'note' => !$smsSent ? 'SMS service not configured. Use OTP from this response for testing.' : null,
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Verify OTP code
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tel' => 'required|string|max:20',
            'otp' => 'required|string|size:6',
            'session_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $tel = $request->tel;
        $otp = $request->otp;
        $sessionId = $request->session_id;

        // Verify OTP
        $isValid = $this->verifyOtpCode($tel, $otp, $sessionId);

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP code or session expired',
                'errors' => [
                    'otp' => ['The OTP code is invalid or has expired']
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
            'data' => [
                'verified' => true,
                'tel' => $tel,
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Format tel for database only (local number, no +855). e.g. +855967772715 -> 967772715
     *
     * @param string $tel Phone number (e.g. +855967772715, 967772715, 0123456789)
     * @return string Local number for DB (e.g. 967772715)
     */
    private function telForDatabase($tel)
    {
        $tel = preg_replace('/[\s\-\(\)]/', '', trim($tel));
        if (str_starts_with($tel, '+855')) {
            return substr($tel, 4);
        }
        if (str_starts_with($tel, '855') && strlen($tel) > 3) {
            return substr($tel, 3);
        }
        if (str_starts_with($tel, '0')) {
            return substr($tel, 1);
        }
        return $tel;
    }

    /**
     * Format tel with +855 for SMS only (Cambodia). Not used for storage or username.
     *
     * @param string $tel Phone number as user entered (e.g. 967772715, 0123456789)
     * @return string Number for SMS provider (e.g. +855967772715)
     */
    private function telForSms($tel)
    {
        $tel = preg_replace('/[\s\-\(\)]/', '', trim($tel));
        if (str_starts_with($tel, '+')) {
            return $tel;
        }
        if (str_starts_with($tel, '0')) {
            return '+855' . substr($tel, 1);
        }
        if (str_starts_with($tel, '855')) {
            return '+' . $tel;
        }
        return '+855' . $tel;
    }

    /**
     * Verify OTP code (private helper method)
     * 
     * @param string $tel Phone number
     * @param string $otp OTP code to verify
     * @param string $sessionId Session ID from OTP request
     * @return bool True if OTP is valid, false otherwise
     */
    private function verifyOtpCode($tel, $otp, $sessionId)
    {
        $verifiedKey = "otp_verified_{$sessionId}";
        if (Cache::get($verifiedKey)) {
            if (config('app.debug')) {
                Log::info('OTP already verified for session (idempotent)', ['session_id' => $sessionId]);
            }
            return true;
        }

        // Cache uses local number only (e.g. 967772715) so +855 or 967772715 both match
        $telForDb = $this->telForDatabase($tel);
        $cacheKey = "otp_{$telForDb}_{$sessionId}";
        $storedOtp = Cache::get($cacheKey);
        
        if (config('app.debug')) {
            Log::info('OTP Verification Attempt', [
                'tel' => $telForDb,
                'session_id' => $sessionId,
                'provided_otp' => $otp,
                'found' => $storedOtp ? 'yes' : 'no',
                'match' => ($storedOtp && $storedOtp === $otp) ? 'yes' : 'no'
            ]);
        }
        
        if ($storedOtp && $storedOtp === $otp) {
            Cache::forget($cacheKey);
            Cache::put($verifiedKey, true, now()->addMinutes(5));
            return true;
        }
        
        return false;
    }

    /**
     * Verify OTP code (private helper method for registration)
     * 
     * @param string $tel Phone number
     * @param string $otp OTP code to verify
     * @param string $sessionId Session ID from OTP request
     * @return bool True if OTP is valid, false otherwise
     */
    private function verifyOtpForRegistration($tel, $otp, $sessionId)
    {
        return $this->verifyOtpCode($tel, $otp, $sessionId);
    }

    /**
     * Login user
     *
     * Login with Phone: { "tel": "0123456789", "password": "...", "login_method": "phone" }
     * Normal Login: { "username": "..." or "email": "..." or "tel": "...", "password": "...", "login_method": "normal" }
     */
    public function login(Request $request)
    {
        $loginMethod = $request->input('login_method', 'normal');

        if ($loginMethod === 'phone') {
            // Login with Phone: tel + password
            $validator = Validator::make($request->all(), [
                'tel' => 'required|string',
                'password' => 'required|string',
            ]);
        } else {
            // Normal Login: at least one of username, email, tel + password
            $validator = Validator::make($request->all(), [
                'password' => 'required|string',
                'username' => 'required_without_all:email,tel|nullable|string',
                'email' => 'required_without_all:username,tel|nullable|email',
                'tel' => 'required_without_all:username,email|nullable|string',
            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = null;

        if ($loginMethod === 'phone') {
            // Find user by tel (DB stores as e.g. 967772715)
            $telForDb = $this->telForDatabase(trim($request->tel));
            $user = User::where('tel', $telForDb)->first();
        } else {
            // Normal: find by username, or email, or tel
            $loginInput = trim($request->username ?? $request->email ?? $request->tel ?? '');
            if ($loginInput === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => ['username' => ['Username, email or tel is required.']]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $telForDb = $this->telForDatabase($loginInput);
            $user = User::where('username', $loginInput)
                ->orWhere('username', $telForDb)
                ->orWhere('email', $loginInput)
                ->orWhere('tel', $loginInput)
                ->orWhere('tel', $telForDb)
                ->first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->password || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $existingToken = $user->tokens()->where('name', 'auth_token')->first();

        if ($existingToken && $existingToken->plainTextToken) {
            return response()->json([
                'success' => true,
                'message' => 'User already logged in',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'tel' => $user->tel,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'access_token' => $existingToken->plainTextToken,
                    'token_type' => 'Bearer',
                    'already_logged_in' => true,
                ]
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'tel' => $user->tel,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
                'already_logged_in' => false,
            ]
        ]);
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'User profile retrieved successfully',
            'data' => [
                'id' => $request->user()->id,
                'username' => $request->user()->username,
                'tel' => $request->user()->tel,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ]
        ]);
    }

    /**
     * Logout user (remove current token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out from all devices successfully'
        ]);
    }

    /**
     * Check if user is authenticated
     */
    public function checkAuth(Request $request)
    {
        if ($request->user()) {
            return response()->json([
                'success' => true,
                'message' => 'User is authenticated',
                'data' => [
                    'id' => $request->user()->id,
                    'username' => $request->user()->username,
                    'tel' => $request->user()->tel,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User is not authenticated'
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Send welcome message to new user
     */
    private function sendWelcomeMessage(User $user)
    {
        try {
            // Get the admin user (assuming admin has user_type = 'admin')
            $admin = User::where('user_type', 'admin')->first();
            
            if (!$admin) {
                // If no admin found, skip sending welcome message
                return;
            }

            // Create welcome message
            $welcomeMessage = "Hey " . ($user->name ?? $user->username) . "! Welcome to our app!";
            
            Message::create([
                'sender_id' => $admin->id,
                'receiver_id' => $user->id,
                'body' => $welcomeMessage,
                'is_read' => false,
                'read_at' => null,
            ]);

        } catch (\Exception $e) {
            // Log error but don't fail registration
            Log::error('Failed to send welcome message: ' . $e->getMessage());
        }
    }
}
