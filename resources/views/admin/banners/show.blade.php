@extends('admin.layouts.app')

@section('page-title', 'Banner Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Banner Details</h1>
                <p class="text-gray-600">View and manage banner information</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.banners.edit', $banner) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                    <i class="fas fa-edit"></i>
                    <span>Edit Banner</span>
                </a>
                <a href="{{ route('admin.banners.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Banners</span>
                </a>
            </div>
        </div>

        <!-- Banner Information -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Banner Image -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Banner Image</h3>
                <div class="space-y-4">
                    <div class="aspect-w-16 aspect-h-9">
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title ?? 'Banner' }}" class="w-full h-auto rounded-lg shadow-sm">
                    </div>
                    @if($banner->link)
                        <div class="text-center">
                            <a href="{{ $banner->link }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                                <i class="fas fa-external-link-alt mr-2"></i>
                                View Link
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Banner Details -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Banner Information</h3>
                <div class="space-y-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Title</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $banner->title ?? 'No Title' }}
                        </p>
                    </div>

                    <!-- Link -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Link</label>
                        <p class="mt-1 text-sm text-gray-900">
                            @if($banner->link)
                                <a href="{{ $banner->link }}" target="_blank" class="text-blue-600 hover:text-blue-800 break-all">
                                    {{ $banner->link }}
                                </a>
                            @else
                                <span class="text-gray-400">No Link</span>
                            @endif
                        </p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Status</label>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $banner->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $banner->status ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <!-- Created Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Created</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $banner->created_at->format('F d, Y \a\t g:i A') }}
                        </p>
                    </div>

                    <!-- Updated Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Last Updated</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $banner->updated_at->format('F d, Y \a\t g:i A') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.banners.edit', $banner) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                    <i class="fas fa-edit"></i>
                    <span>Edit Banner</span>
                </a>
                
                <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this banner? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                        <i class="fas fa-trash"></i>
                        <span>Delete Banner</span>
                    </button>
                </form>

                @if($banner->link)
                    <a href="{{ $banner->link }}" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Visit Link</span>
                    </a>
                @endif

                <a href="{{ route('admin.banners.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                    <i class="fas fa-list"></i>
                    <span>View All Banners</span>
                </a>
            </div>
        </div>

        <!-- Banner Preview -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Banner Preview</h3>
            <div class="bg-gray-100 p-4 rounded-lg">
                <div class="max-w-2xl mx-auto">
                    @if($banner->link)
                        <a href="{{ $banner->link }}" target="_blank">
                            <img src="{{ $banner->image_url }}" alt="{{ $banner->title ?? 'Banner' }}" class="w-full h-auto rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        </a>
                    @else
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title ?? 'Banner' }}" class="w-full h-auto rounded-lg shadow-sm">
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
