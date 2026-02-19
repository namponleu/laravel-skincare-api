<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\MessageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// OTP Management (Public - needed for registration)
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/check-auth', [AuthController::class, 'checkAuth']);
    
    // Order Management
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::delete('/orders/{id}', [OrderController::class, 'destroy']);
    
    // Device Token Management
    Route::post('/device/save-token', [DeviceController::class, 'saveDeviceToken']);
    Route::post('/device/remove-token', [DeviceController::class, 'removeDeviceToken']);
    Route::get('/device/tokens', [DeviceController::class, 'getUserDevices']);
    Route::post('/device/deactivate-token', [DeviceController::class, 'deactivateDeviceToken']);
    
    // Messaging
    Route::post('/messages/send', [MessageController::class, 'sendMessage']);
    Route::post('/messages/send-to-all', [MessageController::class, 'sendMessageToAll']);
    Route::get('/messages/conversations', [MessageController::class, 'getConversations']);
    Route::get('/messages/{user_id}', [MessageController::class, 'getMessages']);
    Route::post('/messages/mark-read', [MessageController::class, 'markAsRead']);
    Route::get('/messages/unread/count', [MessageController::class, 'getUnreadCount']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public product endpoints (no authentication required)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/categories', [ProductController::class, 'categories']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/category/{category}', [ProductController::class, 'getByCategory']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Public banner endpoints (no authentication required)
Route::get('/banners', [BannerController::class, 'index']);
Route::get('/banners/{id}', [BannerController::class, 'show']);
Route::get('/banners/status/{status}', [BannerController::class, 'getByStatus']);
Route::get('/banners/featured/{limit?}', [BannerController::class, 'featured']);