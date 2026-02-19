<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Message;
use App\Models\UserDevice;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Show admin messaging dashboard
     */
    public function index()
    {
        $totalUsers = User::where('user_type', 'user')->count();
        $activeDevices = UserDevice::where('is_active', true)->count();
        $totalMessages = Message::count();
        $recentMessages = Message::with(['sender', 'receiver'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.messages.index', compact(
            'totalUsers', 
            'activeDevices', 
            'totalMessages', 
            'recentMessages'
        ));
    }

    /**
     * Show broadcast message form
     */
    public function create()
    {
        $userTypes = User::select('user_type')
            ->distinct()
            ->pluck('user_type')
            ->filter()
            ->values();

        return view('admin.messages.create', compact('userTypes'));
    }

    /**
     * Send broadcast message to all users
     */
    public function broadcastAll(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'send_push' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Get all users (excluding admins)
            $users = User::where('user_type', 'user')->get();
            $sentCount = 0;
            $pushSentCount = 0;

            foreach ($users as $user) {
                // Save message to database
                Message::create([
                    'sender_id' => Auth::id(),
                    'receiver_id' => $user->id,
                    'body' => $request->message,
                ]);
                $sentCount++;

                // Send push notification if requested
                if ($request->boolean('send_push')) {
                    $devices = UserDevice::where('user_id', $user->id)
                        ->where('is_active', true)
                        ->get();

                    if ($devices->isNotEmpty()) {
                        $deviceTokens = $devices->pluck('device_token')->toArray();
                        
                        FCMService::sendToMultiple(
                            $deviceTokens,
                            $request->title,
                            $request->message,
                            [
                                'type' => 'admin_broadcast',
                                'admin_id' => Auth::id(),
                                'admin_name' => Auth::user()->name
                            ]
                        );
                        $pushSentCount++;
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.messages.index')
                ->with('success', "Broadcast sent successfully! Messages: {$sentCount}, Push notifications: {$pushSentCount}");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to send broadcast: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Send message to specific user type
     */
    public function broadcastToType(Request $request)
    {
        $request->validate([
            'user_type' => 'required|string|in:user,admin',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'send_push' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $users = User::where('user_type', $request->user_type)->get();
            $sentCount = 0;
            $pushSentCount = 0;

            foreach ($users as $user) {
                Message::create([
                    'sender_id' => Auth::id(),
                    'receiver_id' => $user->id,
                    'body' => $request->message,
                ]);
                $sentCount++;

                if ($request->boolean('send_push')) {
                    $devices = UserDevice::where('user_id', $user->id)
                        ->where('is_active', true)
                        ->get();

                    if ($devices->isNotEmpty()) {
                        $deviceTokens = $devices->pluck('device_token')->toArray();
                        
                        FCMService::sendToMultiple(
                            $deviceTokens,
                            $request->title,
                            $request->message,
                            [
                                'type' => 'admin_broadcast',
                                'admin_id' => Auth::id(),
                                'admin_name' => Auth::user()->name
                            ]
                        );
                        $pushSentCount++;
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.messages.index')
                ->with('success', "Broadcast to {$request->user_type}s sent! Messages: {$sentCount}, Push notifications: {$pushSentCount}");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to send broadcast: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Send message to specific user
     */
    public function sendToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
            'send_push' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->user_id);

            // Save message
            Message::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $user->id,
                'body' => $request->message,
            ]);

            $pushSent = false;
            if ($request->boolean('send_push')) {
                $devices = UserDevice::where('user_id', $user->id)
                    ->where('is_active', true)
                    ->get();

                if ($devices->isNotEmpty()) {
                    $deviceTokens = $devices->pluck('device_token')->toArray();
                    
                    FCMService::sendToMultiple(
                        $deviceTokens,
                        "Message from Admin",
                        $request->message,
                        [
                            'type' => 'admin_message',
                            'admin_id' => Auth::id(),
                            'admin_name' => Auth::user()->name
                        ]
                    );
                    $pushSent = true;
                }
            }

            DB::commit();

            $message = "Message sent to {$user->name}";
            if ($pushSent) {
                $message .= " with push notification";
            }

            return redirect()->route('admin.messages.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to send message: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get users for AJAX requests
     */
    public function getUsers(Request $request)
    {
        $query = User::where('user_type', 'user');
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->select('id', 'name', 'username', 'email')
            ->limit(20)
            ->get();

        return response()->json($users);
    }
}
