<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard
     */
    public function dashboard()
    {
        // Get statistics for dashboard
        $totalUsers = User::count();
        $activeUsers = User::where('created_at', '>=', Carbon::now()->subDays(30))->count();
        $newUsers = User::where('created_at', '>=', Carbon::now()->subDays(7))->count();
        $totalOrders = Order::count();
        
        // Get recent activities (only one line)
        $recentActivities = Activity::getRecent(1);

        return view('admin.dashboard', compact('totalUsers', 'activeUsers', 'newUsers', 'totalOrders', 'recentActivities'));
    }

    /**
     * Show all users
     */
    public function users(Request $request)
    {
        $query = User::query();
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        // Filter by user type
        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('tel', 'like', "%{$search}%");
            });
        }
        
        $users = $query->latest()->paginate(15);
        
        // Preserve filters in pagination links
        $users->appends($request->query());
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show user creation form
     */
    public function createUser()
    {
        return view('admin.users.create');
    }

    /**
     * Store new user
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:users',
            'tel' => 'nullable|string|max:20|unique:users',
            'is_active' => 'boolean',
            'user_type' => 'required|in:user,admin',
        ]);

        $data = $validated;
        $data['is_active'] = $request->has('is_active');

        $user = User::create([
            'username' => $data['username'],
            'password' => bcrypt($data['password']),
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'tel' => $data['tel'] ?? null,
            'is_active' => $data['is_active'],
            'user_type' => $data['user_type'],
        ]);

        // Log user creation activity
        Activity::log(
            'user_created',
            'New user created: ' . $user->username,
            Auth::user()->username,
            'admin',
            ['user_id' => $user->id, 'username' => $user->username]
        );

        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
    }

    /**
     * Show user edit form
     */
    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'tel' => 'nullable|string|max:20|unique:users,tel,' . $user->id,
            'is_active' => 'boolean',
            'user_type' => 'required|in:user,admin',
        ]);

        $data = $validated;
        $data['is_active'] = $request->has('is_active');

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    /**
     * Show user details
     */
    public function showUser(User $user)
    {
        $user->load(['orders']);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user)
    {
        // Check if user has orders
        if ($user->orders()->count() > 0) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete user with existing orders. Please delete orders first.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
    }

    /**
     * Show settings page
     */
    public function settings()
    {
        return view('admin.settings');
    }

    /**
     * Show logs page
     */
    public function logs()
    {
        return view('admin.logs');
    }

    /**
     * Show all orders
     */
    public function orders(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('payment_method', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('username', 'like', "%{$search}%")
                               ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $orders = $query->latest()->paginate(15);
        
        // Preserve filters in pagination links
        $orders->appends($request->query());
            
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Show order details
     */
    public function showOrder(Order $order)
    {
        $order->load(['user', 'orderItems.product']);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show edit order form
     */
    public function editOrder(Order $order)
    {
        $order->load(['user', 'orderItems.product']);
        return view('admin.orders.edit', compact('order'));
    }

    /**
     * Update order
     */
    public function updateOrder(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,canceled',
            'payment_method' => 'nullable|string|max:255',
        ]);

        $order->update([
            'status' => $request->status,
            'payment_method' => $request->payment_method ?? $order->payment_method,
        ]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Delete order
     */
    public function deleteOrder(Order $order)
    {
        $order->delete();
        
        return redirect()->route('admin.orders')
            ->with('success', 'Order deleted successfully.');
    }
} 