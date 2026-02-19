<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Activity;

class AuthController extends Controller
{
    /**
     * Show admin login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.auth.login');
    }

    /**
     * Handle admin login
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            /** @var User $user */
            $user = Auth::user();
            
            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Account is inactive. Please contact administrator.',
                ])->onlyInput('username');
            }
            
            // Check if user has admin type
            if (!$user->isAdmin()) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Access denied. Admin privileges required.',
                ])->onlyInput('username');
            }
            
            $request->session()->regenerate();
            
            // Log admin login activity
            Activity::log(
                'login',
                'Admin login: ' . $user->username,
                $user->username,
                'admin',
                ['ip' => $request->ip(), 'user_agent' => $request->userAgent()]
            );
            
            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Welcome back, ' . $user->username . '!');
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        $username = Auth::user() ? Auth::user()->username : 'Unknown';
        
        // Log admin logout activity before logout
        Activity::log(
            'logout',
            'Admin logout: ' . $username,
            $username,
            'admin',
            ['ip' => $request->ip(), 'user_agent' => $request->userAgent()]
        );
        
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Create default admin user if not exists
     */
    public function createDefaultAdmin()
    {
        $adminExists = User::where('username', 'admin')->exists();
        
        if (!$adminExists) {
            User::create([
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'tel' => '1234567890',
                'is_active' => true,
                'user_type' => 'admin',
            ]);
            
            return 'Default admin user created: username=admin, password=admin123';
        }
        
        return 'Admin user already exists';
    }
}
