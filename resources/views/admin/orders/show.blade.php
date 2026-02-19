@extends('admin.layouts.app')

@section('title', 'Order Details')
@section('page-title', 'Order Details')

@section('content')
<div class="space-y-6">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.orders') }}" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Orders
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Order #{{ $order->id }}</h1>
        </div>
        <div class="flex space-x-3">
            <button class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-edit mr-2"></i>
                Edit Order
            </button>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                <i class="fas fa-print mr-2"></i>
                Print Invoice
            </button>
        </div>
    </div>

    <!-- Order Information -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Summary -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Order Summary</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Order ID</p>
                            <p class="text-lg text-gray-900">#{{ $order->id }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            @if($order->status === 'paid')
                                <span class="inline-flex px-2 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                                    Paid
                                </span>
                            @elseif($order->status === 'pending')
                                <span class="inline-flex px-2 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                    Canceled
                                </span>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Payment Method</p>
                            <p class="text-lg text-gray-900">{{ $order->payment_method ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Amount</p>
                            <p class="text-2xl font-bold text-gray-900">${{ number_format($order->total_amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Created</p>
                            <p class="text-lg text-gray-900">{{ $order->created_at ? $order->created_at->format('M d, Y H:i') : 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Last Updated</p>
                            <p class="text-lg text-gray-900">{{ $order->updated_at ? $order->updated_at->format('M d, Y H:i') : 'N/A' }}</p>
                        </div>
                    </div>
                    
                    <!-- Order Items Preview -->
                    <div class="mt-6">
                        <p class="text-sm font-medium text-gray-500 mb-3">Order Items Preview</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($order->orderItems->take(6) as $item)
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0 h-16 w-16">
                                        @if($item->product && $item->product->image)
                                            <img class="h-16 w-16 rounded-lg object-cover" src="{{ $item->product->image }}" alt="{{ $item->product->name }}">
                                        @else
                                            <div class="h-16 w-16 rounded-lg bg-gray-200 flex items-center justify-center">
                                                <i class="fas fa-coffee text-gray-400"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-900 truncate">
                                            {{ $item->product->name ?? 'Product #' . $item->product_id }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Qty: {{ $item->qty }} | ${{ number_format($item->price, 2) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @if($order->orderItems->count() > 6)
                                <div class="flex items-center justify-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-sm text-gray-500">+{{ $order->orderItems->count() - 6 }} more items</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Order Items ({{ $order->orderItems->count() }})</h3>
                </div>
                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Item
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Size
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Price
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($order->orderItems as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            @if($item->product && $item->product->image)
                                                <img class="h-12 w-12 rounded-lg object-cover" src="{{ $item->product->image }}" alt="{{ $item->product->name }}">
                                            @else
                                                <div class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center">
                                                    <i class="fas fa-coffee text-gray-400"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->product->name ?? 'Product #' . $item->product_id }}</div>
                                            <div class="text-sm text-gray-500">
                                                Item ID: {{ $item->id }}
                                                @if($item->product)
                                                    | Category: {{ $item->product->category }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->size ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->qty }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${{ number_format($item->price, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ${{ number_format($item->total_price, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="space-y-6">
            <!-- Customer Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Customer Information</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Username</p>
                            <p class="text-lg text-gray-900">{{ $order->user->username ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Name</p>
                            <p class="text-lg text-gray-900">{{ $order->user->name ?? 'No name' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Email</p>
                            <p class="text-lg text-gray-900">{{ $order->user->email ?? 'No email' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Phone</p>
                            <p class="text-lg text-gray-900">{{ $order->user->tel ?? 'No phone' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Customer ID</p>
                            <p class="text-lg text-gray-900">#{{ $order->user->id }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    <button class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-envelope mr-2"></i>
                        Send Invoice
                    </button>
                    <button class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-shipping-fast mr-2"></i>
                        Update Status
                    </button>
                    <button class="w-full px-4 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-white hover:bg-red-50">
                        <i class="fas fa-times mr-2"></i>
                        Cancel Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 