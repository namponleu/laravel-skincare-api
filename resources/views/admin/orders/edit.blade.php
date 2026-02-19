@extends('admin.layouts.app')

@section('title', 'Edit Order')
@section('page-title', 'Edit Order')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Order #{{ $order->id }}</h1>
            <p class="mt-2 text-sm text-gray-700">Update order status and payment information</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.orders.show', $order) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-eye mr-2"></i>
                View Order
            </a>
            <a href="{{ route('admin.orders') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Orders
            </a>
        </div>
    </div>

    <!-- Order Information -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Order Information</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Order ID</label>
                <div class="text-sm text-gray-900">#{{ $order->id }}</div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                <div class="text-sm text-gray-900">{{ $order->user->username ?? 'N/A' }}</div>
                <div class="text-sm text-gray-500">{{ $order->user->name ?? 'No name' }}</div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Total Amount</label>
                <div class="text-sm font-medium text-gray-900">${{ number_format($order->total_amount, 2) }}</div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Created Date</label>
                <div class="text-sm text-gray-900">{{ $order->created_at ? $order->created_at->format('M d, Y H:i') : 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <form action="{{ route('admin.orders.update', $order) }}" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        @csrf
        @method('PUT')
        
        <h3 class="text-lg font-medium text-gray-900 mb-4">Update Order</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                    Order Status <span class="text-red-500">*</span>
                </label>
                <select name="status" id="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ $order->status === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="canceled" {{ $order->status === 'canceled' ? 'selected' : '' }}>Canceled</option>
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                    Payment Method
                </label>
                <input type="text" name="payment_method" id="payment_method" 
                       value="{{ old('payment_method', $order->payment_method) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="e.g., Credit Card, Cash, PayPal">
                @error('payment_method')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('admin.orders.show', $order) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>
                Update Order
            </button>
        </div>
    </form>

    <!-- Order Items -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Order Items</h3>
        
        <div class="space-y-4">
            @foreach($order->orderItems as $item)
                <div class="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg">
                    <div class="flex-shrink-0 h-16 w-16">
                        @if($item->product && $item->product->image)
                            <img class="h-16 w-16 rounded-lg object-cover" src="{{ $item->product->image }}" alt="{{ $item->product->name }}">
                        @else
                            <div class="h-16 w-16 rounded-lg bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-coffee text-gray-400"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $item->product->name ?? ($item->product_name ?? 'Product #' . $item->product_id) }}
                        </div>
                        <div class="text-sm text-gray-500">
                            Quantity: {{ $item->qty }} | Price: ${{ number_format($item->price, 2) }}
                            @if($item->size)
                                | Size: {{ $item->size }}
                            @endif
                        </div>
                    </div>
                    <div class="text-sm font-medium text-gray-900">
                        ${{ number_format($item->total_price, 2) }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
