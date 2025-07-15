@extends('layouts.app-content')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.services') }}" class="text-primary-600 hover:text-primary-900 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Back to Services
        </a>
        <h1 class="text-3xl font-bold">Edit Service</h1>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
        <form action="{{ route('admin.services.update', $service) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input id="name" type="text" class="form-input w-full rounded-md shadow-sm @error('name') border-red-500 @enderror"
                    name="name" value="{{ old('name', $service->name) }}" required autofocus>
                @error('name')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" class="form-textarea w-full rounded-md shadow-sm @error('description') border-red-500 @enderror"
                    name="description" rows="3">{{ old('description', $service->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                <div class="flex items-center">
                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                        $
                    </span>
                    <input id="price" type="number" step="0.01" min="0"
                        class="form-input w-full rounded-r-md shadow-sm @error('price') border-red-500 @enderror"
                        name="price" value="{{ old('price', $service->price) }}" required>
                </div>
                @error('price')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes)</label>
                <input id="duration" type="number" min="1"
                    class="form-input w-full rounded-md shadow-sm @error('duration') border-red-500 @enderror"
                    name="duration" value="{{ old('duration', $service->duration) }}" required>
                @error('duration')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Categories</label>
                @if(!$categories->isEmpty())
                    <div class="space-y-2">
                        @foreach($categories as $category)
                            <div class="flex items-center">
                                <input type="checkbox" id="category_{{ $category->id }}"
                                    name="category_ids[]" value="{{ $category->id }}"
                                    class="form-checkbox"
                                    {{ in_array($category->id, old('category_ids', $service->categories->pluck('id')->toArray())) ? 'checked' : '' }}>
                                <label for="category_{{ $category->id }}" class="ml-2 text-sm text-gray-700">
                                    {{ $category->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No categories available. Please create categories first.</p>
                @endif
                @error('category_ids')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="image_url" class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                <input id="image_url" type="url"
                    class="form-input w-full rounded-md shadow-sm @error('image_url') border-red-500 @enderror"
                    name="image_url" value="{{ old('image_url', $service->image_url) }}">
                <p class="text-gray-500 text-xs mt-1">Optional - URL to an image for this service</p>
                @error('image_url')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" class="form-checkbox" name="is_featured" value="1" {{ old('is_featured', $service->is_featured) ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-700">Featured Service</span>
                </label>
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" class="form-checkbox" name="active" value="1" {{ old('active', $service->active) ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded">
                    Update Service
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
