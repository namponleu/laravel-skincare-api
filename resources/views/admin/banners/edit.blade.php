@extends('admin.layouts.app')

@section('page-title', 'Edit Banner')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Banner</h1>
                <p class="text-gray-600">Update banner information and image</p>
            </div>
            <a href="{{ route('admin.banners.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Banners</span>
            </a>
        </div>

        <!-- Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data" class="space-y-6 p-6">
                @csrf
                @method('PUT')
                
                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Banner Title (Optional)</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $banner->title) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           placeholder="Enter banner title">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Image -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Image</label>
                    <div class="max-w-xs">
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title ?? 'Banner' }}" class="w-full h-auto rounded-lg shadow-sm">
                    </div>
                </div>

                <!-- Image URL -->
                <div>
                    <label for="image_url" class="block text-sm font-medium text-gray-700 mb-2">
                        Banner Image URL <span class="text-red-500">*</span>
                    </label>
                    <input type="url" name="image_url" id="image_url" value="{{ old('image_url', $banner->image_url) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           placeholder="https://example.com/banner-image.jpg" required>
                    <p class="mt-1 text-sm text-gray-500">Enter the URL of the banner image</p>
                    @error('image_url')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image Preview -->
                <div id="image-preview">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Image Preview</label>
                    <div class="max-w-xs">
                        <img id="preview-img" src="{{ $banner->image_url }}" alt="Preview" class="w-full h-auto rounded-lg shadow-sm">
                    </div>
                </div>

                <!-- Link -->
                <div>
                    <label for="link" class="block text-sm font-medium text-gray-700 mb-2">Banner Link (Optional)</label>
                    <input type="url" name="link" id="link" value="{{ old('link', $banner->link) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           placeholder="https://example.com">
                    <p class="mt-1 text-sm text-gray-500">Where should this banner link to when clicked?</p>
                    @error('link')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <div class="flex items-center">
                        <input type="checkbox" name="status" id="status" value="1" {{ old('status', $banner->status) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="status" class="ml-2 block text-sm text-gray-900">
                            Active Banner
                        </label>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Active banners will be displayed on your website</p>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.banners.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                        <i class="fas fa-save"></i>
                        <span>Update Banner</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('image_url').addEventListener('input', function(e) {
    const url = e.target.value;
    const previewImg = document.getElementById('preview-img');
    
    if (url && url.startsWith('http')) {
        previewImg.src = url;
    }
});
</script>
@endsection
