<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Laravel API</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        .sidebar-transition {
            transition: all 0.3s ease-in-out;
        }
        .content-transition {
            transition: margin-left 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 sidebar-transition transform -translate-x-full lg:translate-x-0">
        <!-- Logo -->
        <div class="flex items-center justify-center h-16 bg-gray-800">
            <h1 class="text-white text-xl font-bold">Admin Panel</h1>
        </div>
        
        <!-- Navigation -->
        <nav class="mt-8">
            <div class="px-4 space-y-2">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                    Dashboard
                </a>
                
                <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-colors {{ request()->routeIs('admin.users*') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-users w-5 h-5 mr-3"></i>
                    Users
                </a>
                
                <a href="{{ route('admin.orders') }}" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-colors {{ request()->routeIs('admin.orders*') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-shopping-cart w-5 h-5 mr-3"></i>
                    Orders
                </a>
            
                <a href="{{ route('admin.products.index') }}" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-colors {{   request()->routeIs('admin.products*') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-coffee w-5 h-5 mr-3"></i>
                    Products
                </a>
            
                <a href="{{ route('admin.banners.index') }}" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-colors {{ request()->routeIs('admin.banners*') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-images w-5 h-5 mr-3"></i>
                    Banners
                </a>
            
                <a href="{{ route('admin.messages.index') }}" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-colors {{ request()->routeIs('admin.messages*') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-envelope w-5 h-5 mr-3"></i>
                    Messages
                </a>
                
                <a href="{{ route('admin.settings') }}" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-colors {{ request()->routeIs('admin.settings*') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-cog w-5 h-5 mr-3"></i>
                    Settings
                </a>
                
                <a href="{{ route('admin.logs') }}" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-colors {{ request()->routeIs('admin.logs*') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-file-alt w-5 h-5 mr-3"></i>
                    Logs
                </a>
                
                <!-- Logout Menu Item -->
                <form action="{{ route('admin.logout') }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-3 text-gray-300 hover:bg-red-600 hover:text-white rounded-lg transition-colors">
                        <i class="fas fa-sign-out-alt w-5 h-5 mr-3"></i>
                        Logout
                    </button>
                </form>
            </div>
        </nav>
        
        <!-- User Info -->
        <div class="absolute bottom-0 w-full p-4 bg-gray-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full overflow-hidden">
                        <img class="w-full h-full object-cover" src="{{ asset('images/profile_pokemon.png') }}" alt="Profile" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="w-full h-full bg-blue-500 flex items-center justify-center" style="display: none;">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-white text-sm font-medium">{{ Auth::user()->name ?? Auth::user()->username }}</p>
                        <p class="text-gray-400 text-xs">{{ Auth::user()->email ?? Auth::user()->username }}</p>
                        {{-- <p class="text-gray-400 text-xs">{{ Auth::user()->email ?? Auth::user()->username }}@example.com</p> --}}
                    </div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-white p-1 rounded" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Mobile sidebar overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden hidden" onclick="toggleSidebar()"></div>
    
    <!-- Main Content -->
    <div id="main-content" class="lg:ml-64 content-transition">
        <!-- Top Navigation -->
        <header class="bg-white shadow-sm border-b">
            <div class="flex items-center justify-between px-6 py-4">
                <!-- Mobile menu button -->
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                    <i class="fas fa-bars"></i>
                </button>
                
                <!-- Page title -->
                <h2 class="text-xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>
                
                <!-- Right side actions -->
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full relative">
                        <i class="fas fa-bell"></i>
                        <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400"></span>
                    </button>
                    
                    <!-- Profile dropdown -->
                    <div class="relative">
                        <button class="flex items-center space-x-2 p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                            <img class="w-8 h-8 rounded-full bg-gray-300" src="{{ asset('images/profile.png') }}" alt="Profile" onerror="this.src='https://via.placeholder.com/32'">
                            <span class="hidden md:block">Admin</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="p-6">
            @yield('content')
        </main>
    </div>
    
    <!-- JavaScript -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const mainContent = document.getElementById('main-content');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                // Open sidebar
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                mainContent.classList.add('lg:ml-64');
            } else {
                // Close sidebar
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                mainContent.classList.remove('lg:ml-64');
            }
        }
        
        // Close sidebar on window resize if mobile
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                document.getElementById('sidebar').classList.remove('-translate-x-full');
                document.getElementById('sidebar-overlay').classList.add('hidden');
            }
        });
    </script>
</body>
</html> 