@extends('admin.layouts.app')

@section('title', 'Messages Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Messages</h1>
            <p class="text-gray-600 mt-1">Send messages and push notifications to users</p>
        </div>
        <button onclick="showBroadcastModal('all')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-plus mr-2"></i>Send Message
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalUsers }}</p>
                </div>
                <div class="text-blue-500">
                    <i class="fas fa-users text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Active Devices</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $activeDevices }}</p>
                </div>
                <div class="text-green-500">
                    <i class="fas fa-mobile-alt text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Total Messages</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalMessages }}</p>
                </div>
                <div class="text-purple-500">
                    <i class="fas fa-envelope text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Push Ready</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $activeDevices > 0 ? 'Yes' : 'No' }}</p>
                </div>
                <div class="text-yellow-500">
                    <i class="fas fa-bell text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center p-6 border border-gray-200 rounded-lg hover:border-blue-300 transition-colors">
                    <div class="text-blue-500 mb-4">
                        <i class="fas fa-bullhorn text-3xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Broadcast to All</h4>
                    <p class="text-gray-600 mb-4">Send message to all registered users</p>
                    <button onclick="showBroadcastModal('all')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-bullhorn mr-2"></i>Broadcast All
                    </button>
                </div>

                <div class="text-center p-6 border border-gray-200 rounded-lg hover:border-green-300 transition-colors">
                    <div class="text-green-500 mb-4">
                        <i class="fas fa-user text-3xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Send to User</h4>
                    <p class="text-gray-600 mb-4">Send message to a specific user</p>
                    <button onclick="showUserMessageModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-user mr-2"></i>Send to User
                    </button>
                </div>

                <div class="text-center p-6 border border-gray-200 rounded-lg hover:border-purple-300 transition-colors">
                    <div class="text-purple-500 mb-4">
                        <i class="fas fa-filter text-3xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Broadcast by Type</h4>
                    <p class="text-gray-600 mb-4">Send to users or admins only</p>
                    <button onclick="showBroadcastModal('type')" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-filter mr-2"></i>Broadcast by Type
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Messages -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Recent Messages</h3>
        </div>
        <div class="p-6">
            @if($recentMessages->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">To</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentMessages as $message)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $message->sender->user_type === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $message->sender->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($message->receiver)
                                        {{ $message->receiver->name ?? $message->receiver->username ?? 'User #' . $message->receiver->id }}
                                    @else
                                        <span class="text-red-500">User not found</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ Str::limit($message->body, 50) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($message->is_read)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Read
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Unread
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $message->created_at->format('M d, Y H:i') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-envelope text-6xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No messages yet</h3>
                    <p class="text-gray-600">Start by sending your first message to users</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Broadcast All Modal -->
<div id="broadcastAllModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <form action="{{ route('admin.messages.broadcast-all') }}" method="POST">
                @csrf
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Broadcast to All Users</h3>
                        <button type="button" onclick="closeModal('broadcastAllModal')" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Notification Title</label>
                        <input type="text" id="title" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea id="message" name="message" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="send_push" name="send_push" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="send_push" class="ml-2 block text-sm text-gray-700">
                            Send push notification
                        </label>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('broadcastAllModal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Send Broadcast
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Broadcast by Type Modal -->
<div id="broadcastTypeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <form action="{{ route('admin.messages.broadcast-type') }}" method="POST">
                @csrf
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Broadcast by User Type</h3>
                        <button type="button" onclick="closeModal('broadcastTypeModal')" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label for="user_type" class="block text-sm font-medium text-gray-700 mb-1">User Type</label>
                        <select id="user_type" name="user_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select user type</option>
                            <option value="user">Regular Users</option>
                            <option value="admin">Admins</option>
                        </select>
                    </div>
                    <div>
                        <label for="title_type" class="block text-sm font-medium text-gray-700 mb-1">Notification Title</label>
                        <input type="text" id="title_type" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="message_type" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea id="message_type" name="message" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="send_push_type" name="send_push" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="send_push_type" class="ml-2 block text-sm text-gray-700">
                            Send push notification
                        </label>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('broadcastTypeModal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Send Broadcast
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Send to User Modal -->
<div id="userMessageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <form action="{{ route('admin.messages.send-user') }}" method="POST">
                @csrf
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Send Message to User</h3>
                        <button type="button" onclick="closeModal('userMessageModal')" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label for="user_search" class="block text-sm font-medium text-gray-700 mb-1">Search User</label>
                        <input type="text" id="user_search" placeholder="Type to search users..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <div id="user_results" class="mt-2"></div>
                        <input type="hidden" id="selected_user_id" name="user_id">
                    </div>
                    <div>
                        <label for="message_user" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea id="message_user" name="message" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="send_push_user" name="send_push" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="send_push_user" class="ml-2 block text-sm text-gray-700">
                            Send push notification
                        </label>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('userMessageModal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

<script>
// Global functions for modal handling
function showBroadcastModal(type) {
    if (type === 'all') {
        document.getElementById('broadcastAllModal').classList.remove('hidden');
    } else if (type === 'type') {
        document.getElementById('broadcastTypeModal').classList.remove('hidden');
    }
}

function showUserMessageModal() {
    document.getElementById('userMessageModal').classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // User search functionality
    const userSearch = document.getElementById('user_search');
    const userResults = document.getElementById('user_results');
    const selectedUserId = document.getElementById('selected_user_id');
    
    if (userSearch) {
        userSearch.addEventListener('input', function() {
            const query = this.value;
            if (query.length > 2) {
                fetch(`{{ route("admin.messages.index") }}?search=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(users => {
                        console.log('Search results:', users); // Debug log
                        let html = '';
                        if (users.length === 0) {
                            html = '<div class="p-2 text-gray-500 text-sm">No users found</div>';
                        } else {
                            users.forEach(function(user) {
                                console.log('User data:', user); // Debug log
                                html += `<div class="user-option p-2 border-b border-gray-200 cursor-pointer hover:bg-gray-50" data-user-id="${user.id}" data-user-name="${user.name || user.username}">
                                    <div class="font-medium text-gray-900">${user.name || user.username || 'Unknown User'}</div>
                                    <div class="text-sm text-gray-500">${user.username || 'No username'} • ${user.email || 'No email'}</div>
                                </div>`;
                            });
                        }
                        userResults.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error searching users:', error);
                    });
            } else {
                userResults.innerHTML = '';
            }
        });
    }
    
    // Handle user selection
    document.addEventListener('click', function(e) {
        if (e.target.closest('.user-option')) {
            const userOption = e.target.closest('.user-option');
            const userId = userOption.dataset.userId;
            const userName = userOption.dataset.userName;
            selectedUserId.value = userId;
            userSearch.value = userName;
            userResults.innerHTML = '';
        }
    });
});
</script>
