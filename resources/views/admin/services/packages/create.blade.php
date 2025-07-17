@extends('layouts.admin')

@section('title', 'Create Service Package')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--multiple {
        border-color: #d1d5db;
        border-radius: 0.375rem;
        min-height: 42px;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #3b82f6;
        outline: 0;
        box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.5);
    }
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.services.packages') }}" class="mr-2 text-gray-500 hover:text-blue-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Create Service Package</h1>
    </div>

    @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Please fix the following errors:</p>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('admin.services.packages.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Package Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                </div>
                
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id" id="category_id" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Package Price <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="price" id="price" value="{{ old('price') }}" step="0.01" min="0" required
                            class="w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>
                </div>
                
                <div>
                    <label for="discount_percentage" class="block text-sm font-medium text-gray-700 mb-1">Discount Percentage</label>
                    <div class="relative">
                        <input type="number" name="discount_percentage" id="discount_percentage" value="{{ old('discount_percentage', 0) }}" step="0.01" min="0" max="100"
                            class="w-full pr-12 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">%</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Optional. Leave at 0 if no discount.</p>
                </div>
            </div>
            
            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" id="description" rows="3" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">{{ old('description') }}</textarea>
            </div>
            
            <div class="mt-6">
                <label for="services" class="block text-sm font-medium text-gray-700 mb-1">Services <span class="text-red-500">*</span></label>
                <select name="services[]" id="services" multiple required
                    class="services-select w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ in_array($service->id, old('services', [])) ? 'selected' : '' }}>
                            {{ $service->name }} - ${{ number_format($service->price, 2) }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Select all services that should be included in this package.</p>
            </div>
            
            <div class="mt-6">
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                </div>
                <p class="mt-1 text-xs text-gray-500">Inactive packages won't be visible to clients.</p>
            </div>
            
            <div class="mt-8 flex justify-end">
                <a href="{{ route('admin.services.packages') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md mr-2">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                    Create Package
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.services-select').select2({
            placeholder: 'Select services to include in the package',
            allowClear: true
        });
    });
</script>
@endsection
