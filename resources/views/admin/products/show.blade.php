@extends('admin.layouts.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <a href="{{ route('admin.products.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Product Details</h1>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.products.edit', $product) }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-edit mr-2"></i>Edit Product
            </a>
            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline" 
                  onsubmit="return confirm('Are you sure you want to delete this product?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-trash mr-2"></i>Delete Product
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Product Image -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Product Image</h2>
            @if($product->image)
                <div class="w-full h-80 border-2 border-gray-200 rounded-lg overflow-hidden">
                    <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                </div>
            @else
                <div class="w-full h-80 border-2 border-gray-200 border-dashed rounded-lg flex items-center justify-center">
                    <div class="text-center">
                        <i class="fas fa-coffee text-6xl text-gray-400 mb-4"></i>
                        <p class="text-gray-500">No image available</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Product Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Product Information</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600">Product Name</label>
                    <p class="text-lg font-semibold text-gray-900">{{ $product->name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600">Category</label>
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ $product->category }}
                    </span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600">Price</label>
                    <p class="text-2xl font-bold text-green-600">${{ number_format($product->price, 2) }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600">Stock</label>
                    <p class="text-lg font-semibold text-gray-900">{{ $product->stock ?? 0 }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600">Rating</label>
                    <div class="flex items-center">
                        <span class="text-lg font-semibold text-gray-900 mr-3">{{ $product->rate }}</span>
                        <div class="flex text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $product->rate)
                                    <i class="fas fa-star"></i>
                                @else
                                    <i class="far fa-star"></i>
                                @endif
                            @endfor
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600">Status</label>
                    @if($product->is_active)
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                            Active
                        </span>
                    @else
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                            Inactive
                        </span>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600">Description</label>
                    <p class="text-gray-700 leading-relaxed">{{ $product->description }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600">Created</label>
                    <p class="text-gray-700">{{ $product->created_at->format('F j, Y \a\t g:i A') }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600">Last Updated</label>
                    <p class="text-gray-700">{{ $product->updated_at->format('F j, Y \a\t g:i A') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.products.edit', $product) }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-edit mr-2"></i>Edit Product
            </a>
            <a href="{{ route('admin.products.index') }}" 
               class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-list mr-2"></i>Back to Products
            </a>
            <a href="{{ route('admin.dashboard') }}" 
               class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-home mr-2"></i>Dashboard
            </a>
        </div>
    </div>
</div>
@endsection 