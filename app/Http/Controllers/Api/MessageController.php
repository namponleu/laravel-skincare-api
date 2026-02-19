<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Models\UserDevice;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Send a message to another user (Admin only)
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        /** @var User|null $user */
        $user = Auth::user();

        // Only admin can send messages
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can send messages'
            ], 403);
        }

        // Prevent sending message to self
        if ($request->receiver_id == $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot send message to yourself'
            ], 400);
        }

        DB::beginTransaction();
        
        try {
            // Save message
            $message = Message::create([
                'sender_id' => $user->id,
                'receiver_id' => $request->receiver_id,
                'body' => $request->message,
            ]);

            // Get receiver's active device tokens
            $receiverDevices = UserDevice::where('user_id', $request->receiver_id)
                ->where('is_active', true)
                ->get();

            // Send push notification to all receiver's devices
            if ($receiverDevices->isNotEmpty()) {
                $deviceTokens = $receiverDevices->pluck('device_token')->toArray();
                
                FCMService::sendToMultiple(
                    $deviceTokens,
                    "New message from " . $user->name,
                    $request->message,
                    [
                        'message_id' => $message->id,
                        'sender_id' => $user->id,
                        'sender_name' => $user->name,
                        'type' => 'admin_message'
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $message->load(['sender', 'receiver'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send message to all users (Admin only)
     */
    public function sendMessageToAll(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        /** @var User|null $user */
        $user = Auth::user();

        // Only admin can send messages to all users
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can send messages to all users'
            ], 403);
        }

        DB::beginTransaction();
        
        try {
            // Get all active users except admin
            $allUsers = User::where('user_type', 'user')
                ->where('is_active', true)
                ->get();

            $messages = [];
            $allDeviceTokens = [];

            // Create message for each user
            foreach ($allUsers as $receiver) {
                $message = Message::create([
                    'sender_id' => $user->id,
                    'receiver_id' => $receiver->id,
                    'body' => $request->message,
                ]);

                $messages[] = $message;

                // Collect device tokens for push notifications
                $receiverDevices = UserDevice::where('user_id', $receiver->id)
                    ->where('is_active', true)
                    ->get();

                if ($receiverDevices->isNotEmpty()) {
                    $deviceTokens = $receiverDevices->pluck('device_token')->toArray();
                    $allDeviceTokens = array_merge($allDeviceTokens, $deviceTokens);
                }
            }

            // Send push notification to all users' devices
            if (!empty($allDeviceTokens)) {
                FCMService::sendToMultiple(
                    $allDeviceTokens,
                    "Announcement from " . $user->name,
                    $request->message,
                    [
                        'sender_id' => $user->id,
                        'sender_name' => $user->name,
                        'type' => 'admin_announcement'
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Message sent to ' . count($allUsers) . ' users successfully',
                'data' => [
                    'sent_to_count' => count($allUsers),
                    'messages_created' => count($messages)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message to all users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's conversations (messages with admin for regular users, all conversations for admin)
     */
    public function getConversations(): JsonResponse
    {
        try {
            /** @var User|null $user */
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            if ($user->isAdmin()) {
                // Admin can see all conversations
                $conversations = Message::select([
                        DB::raw("CASE 
                            WHEN sender_id = {$user->id} THEN receiver_id 
                            ELSE sender_id 
                        END as other_user_id"),
                        DB::raw('MAX(created_at) as last_message_at'),
                        DB::raw('COUNT(*) as message_count')
                    ])
                    ->where(function($query) use ($user) {
                        $query->where('sender_id', $user->id)
                              ->orWhere('receiver_id', $user->id);
                    })
                    ->groupBy('other_user_id')
                    ->orderBy('last_message_at', 'desc')
                    ->get();
            } else {
                // Regular users can only see conversations with admin
                $adminId = User::where('user_type', 'admin')->first()->id ?? null;
                
                if (!$adminId) {
                    return response()->json([
                        'success' => true,
                        'conversations' => []
                    ]);
                }

                $conversations = Message::select([
                        DB::raw("{$adminId} as other_user_id"),
                        DB::raw('MAX(created_at) as last_message_at'),
                        DB::raw('COUNT(*) as message_count')
                    ])
                    ->where(function($query) use ($user, $adminId) {
                        $query->where('sender_id', $user->id)
                              ->where('receiver_id', $adminId);
                    })
                    ->orWhere(function($query) use ($user, $adminId) {
                        $query->where('sender_id', $adminId)
                              ->where('receiver_id', $user->id);
                    })
                    ->groupBy('other_user_id')
                    ->orderBy('last_message_at', 'desc')
                    ->get();
            }

            // Get user details for each conversation
            $conversationsWithUsers = $conversations->map(function($conversation) {
                $otherUser = User::select('id', 'name', 'username', 'email', 'user_type')
                    ->find($conversation->other_user_id);
                
                return [
                    'other_user_id' => $conversation->other_user_id,
                    'last_message_at' => $conversation->last_message_at,
                    'message_count' => $conversation->message_count,
                    'other_user' => $otherUser
                ];
            });

            return response()->json([
                'success' => true,
                'conversations' => $conversationsWithUsers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching conversations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get messages between current user and another user
     */
    public function getMessages(Request $request, $user_id): JsonResponse
    {
        // Validate the URL parameter
        $validator = Validator::make(['user_id' => $user_id], [
            'user_id' => 'required|exists:users,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid user ID',
                'errors' => $validator->errors()
            ], 422);
        }

        /** @var User|null $user */
        $user = Auth::user();
        $otherUserId = $user_id; // Get from URL parameter
        $perPage = $request->get('per_page', 20);

        // Check if user can access messages with the specified user
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        if ($user->isAdmin()) {
            // Admin can see messages with any user
            $messages = Message::where(function($query) use ($user, $otherUserId) {
                    $query->where('sender_id', $user->id)
                          ->where('receiver_id', $otherUserId);
                })
                ->orWhere(function($query) use ($user, $otherUserId) {
                    $query->where('sender_id', $otherUserId)
                          ->where('receiver_id', $user->id);
                })
                ->with(['sender:id,name,username,user_type', 'receiver:id,name,username,user_type'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        } else {
            // Regular users can only see messages with admin
            $adminId = User::where('user_type', 'admin')->first()->id ?? null;
            
            if ($otherUserId != $adminId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only view messages with administrators'
                ], 403);
            }

            $messages = Message::where(function($query) use ($user, $adminId) {
                    $query->where('sender_id', $user->id)
                          ->where('receiver_id', $adminId);
                })
                ->orWhere(function($query) use ($user, $adminId) {
                    $query->where('sender_id', $adminId)
                          ->where('receiver_id', $user->id);
                })
                ->with(['sender:id,name,username,user_type', 'receiver:id,name,username,user_type'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        }

        // Mark messages as read (only mark messages received by current user)
        Message::where('sender_id', $otherUserId)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'message_id' => 'nullable|exists:messages,id',
            'sender_id' => 'nullable|exists:users,id',
        ]);

        /** @var User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        $userId = $user->id;

        if ($request->filled('message_id')) {
            // Mark specific message as read
            $message = Message::where('id', $request->message_id)
                ->where('receiver_id', $userId)
                ->first();

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found or not authorized'
                ], 404);
            }

            $message->update([
                'is_read' => true,
                'read_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message marked as read'
            ]);

        } elseif ($request->filled('sender_id')) {
            // Mark all messages from specific sender as read
            $updated = Message::where('sender_id', $request->sender_id)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => "Marked {$updated} messages as read"
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Either message_id or sender_id is required'
        ], 400);
    }

    /**
     * Get unread message count
     */
    public function getUnreadCount(): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        if ($user->isAdmin()) {
            // Admin can see unread count from all users
            $unreadCount = Message::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();
        } else {
            // Regular users only see unread count from admin
            $adminId = User::where('user_type', 'admin')->first()->id ?? null;
            
            if (!$adminId) {
                $unreadCount = 0;
            } else {
                $unreadCount = Message::where('receiver_id', $user->id)
                    ->where('sender_id', $adminId)
                    ->where('is_read', false)
                    ->count();
            }
        }

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount
        ]);
    }
}
