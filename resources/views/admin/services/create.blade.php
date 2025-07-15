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
        <h1 class="text-3xl font-bold">Add Service</h1>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
        @php
            $user = auth()->user();
            $company = $user->primaryCompany();
            $availableServices = \App\Models\Service::whereDoesntHave('companies', function($query) use ($company) {
                $query->where('company_id', $company->id);
            })->with('categories')->get();
        @endphp

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Add Service</label>
            <div class="flex space-x-4">
                <button type="button" onclick="showExistingServices()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Use Existing Service</button>
                <button type="button" onclick="showNewServiceForm()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Create New Service</button>
            </div>
        </div>

        <div id="existingServices" class="mb-6 hidden">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Existing Service</label>
                <select name="existing_service_id" id="selected_service_id" class="form-select w-full rounded-md shadow-sm">
                    <option value="">Select a service...</option>
                    @foreach($availableServices as $service)
                        <option value="{{ $service->id }}" data-categories="{{ json_encode($service->categories->pluck('id')->toArray()) }}">
                            {{ $service->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div id="existingServiceCategories" class="mb-4 hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Categories</label>
                <div id="selectedServiceCategories">
                    <!-- Categories will be populated dynamically -->
                </div>
            </div>
        </div>

        <div id="newServiceForm" class="mb-6">
            <form action="{{ route('admin.services.store') }}" method="POST" id="newServiceForm">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input id="name" type="text" class="form-input w-full rounded-md shadow-sm @error('name') border-red-500 @enderror"
                    name="name" value="{{ old('name') }}" required autofocus>
                @error('name')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" class="form-textarea w-full rounded-md shadow-sm @error('description') border-red-500 @enderror"
                    name="description" rows="3">{{ old('description') }}</textarea>
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
                        name="price" value="{{ old('price') }}" required>
                </div>
                @error('price')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes)</label>
                <input id="duration" type="number" min="1"
                    class="form-input w-full rounded-md shadow-sm @error('duration') border-red-500 @enderror"
                    name="duration" value="{{ old('duration') }}" required>
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
                                    {{ in_array($category->id, old('category_ids', [])) ? 'checked' : '' }}>
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
                    name="image_url" value="{{ old('image_url') }}">
                <p class="text-gray-500 text-xs mt-1">Optional - URL to an image for this service</p>
                @error('image_url')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" class="form-checkbox" name="is_featured" value="1" {{ old('is_featured', false) ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-700">Featured Service</span>
                </label>
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" class="form-checkbox" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded">
                    Create Service
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show new service form by default
    showNewServiceForm();

    function showExistingServices() {
        document.getElementById('existingServices').classList.remove('hidden');
        document.getElementById('newServiceForm').classList.add('hidden');
        document.getElementById('existing_service_id').focus();
    }

    function showNewServiceForm() {
        document.getElementById('existingServices').classList.add('hidden');
        document.getElementById('newServiceForm').classList.remove('hidden');
        document.getElementById('name').focus();
    }

    // Handle existing service selection
    const selectedServiceSelect = document.getElementById('selected_service_id');
    selectedServiceSelect.addEventListener('change', function() {
        const selectedServiceId = this.value;
        const selectedServiceCategories = this.options[this.selectedIndex].dataset.categories;

        if (selectedServiceId) {
            // Show selected categories
            document.getElementById('existingServiceCategories').classList.remove('hidden');
            const categoriesDiv = document.getElementById('selectedServiceCategories');
            categoriesDiv.innerHTML = '';

            const categories = JSON.parse(selectedServiceCategories);
            categories.forEach(categoryId => {
                const category = document.createElement('div');
                category.className = 'inline-block bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full mr-2';
                category.textContent = getCategoryName(categoryId);
                categoriesDiv.appendChild(category);
            });
        } else {
            document.getElementById('existingServiceCategories').classList.add('hidden');
        }
    });

    function getCategoryName(categoryId) {
        // You would typically fetch this from your backend
        // For now, returning a placeholder
        return 'Category ' + categoryId;
    }
});
</script>
@endsection
