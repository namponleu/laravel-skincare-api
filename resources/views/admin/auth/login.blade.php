<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Laravel API</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div class="mx-auto h-20 w-20 bg-linear-to-r from-blue-600 to-blue-700 rounded-full flex items-center justify-center shadow-lg">
            {{-- <div class="mx-auto h-20 w-20 bg-gradient-to-r from-blue-600 to-blue-700 rounded-full flex items-center justify-center shadow-lg"> --}}
                <i class="fas fa-solid fa-spa text-pink-500 text-3xl"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Admin Login
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                <i class="fas fa-key mr-1"></i>
                Sign in to access the admin dashboard
            </p>
        </div>

        <!-- Login Form -->
        <div class="bg-white py-8 px-6 shadow rounded-lg">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <form class="space-y-6" action="{{ route('admin.login') }}" method="POST">
                @csrf
                
                <!-- Username Field -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-pink-600"></i>
                        Username
                    </label>
                    <input id="username" name="username" type="text" autocomplete="username" required
                        value="{{ old('username') }}"
                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500  focus:border-pink-500 sm:text-sm @error('username') border-pink-500 @enderror"
                        placeholder="Enter your username">
                    @error('username')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-pink-600"></i>
                        Password
                    </label>
                    <div class="relative">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="w-full px-3 py-2 pr-10 border rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 sm:text-sm @error('password') border-pink-500 @enderror"
                            placeholder="Enter your password">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" onclick="togglePassword()" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i id="password-toggle-icon" class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" 
                        class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">
                        Remember me
                    </label>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                        {{-- <i class="fas fa-sign-in-alt mr-2"></i> --}}
                        Sign in
                        <!-- <i class="fas fa-arrow-right ml-2"></i> -->
                    </button>
                </div>
            </form>

            <!-- Default Admin Credentials -->
            {{-- <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                <h3 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-pink-600"></i>
                    Default Admin Credentials:
                </h3>
                <div class="text-sm text-gray-600 space-y-2">
                    <p class="flex items-center">
                        <i class="fas fa-user mr-2 text-pink-500"></i>
                        <strong>Username:</strong> <span class="ml-1 font-mono bg-gray-100 px-2 py-1 rounded">admin</span>
                    </p>
                    <p class="flex items-center">
                        <i class="fas fa-key mr-2 text-pink-500"></i>
                        <strong>Password:</strong> <span class="ml-1 font-mono bg-gray-100 px-2 py-1 rounded">admin123</span>
                    </p>
                </div>
                <p class="text-xs text-gray-500 mt-3 flex items-center">
                    <i class="fas fa-shield-alt mr-1 text-orange-500"></i>
                    Please change these credentials after first login for security.
                </p>
            </div> --}}
        </div>

        <!-- Footer -->
        <div class="text-center">
            <p class="text-sm text-gray-600 flex items-center justify-center">
                <i class="fas fa-copyright mr-1"></i>
                {{ date('Y') }} Laravel API. All rights reserved.
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('password-toggle-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
