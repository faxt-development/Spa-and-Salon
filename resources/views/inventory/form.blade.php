@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">
            {{ isset($product) ? 'Edit Product' : 'Add New Product' }}
        </h1>
        
        <form action="{{ isset($product) ? route('inventory.products.update', $product) : route('inventory.products.store') }}" 
              method="POST" 
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf
            @if(isset($product))
                @method('PUT')
            @endif

            <div class="bg-white shadow rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Product Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Product Name *</label>
                            <input type="text" name="name" id="name" 
                                   value="{{ old('name', $product->name ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- SKU -->
                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-700">SKU *</label>
                            <input type="text" name="sku" id="sku" 
                                   value="{{ old('sku', $product->sku ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   required>
                            @error('sku')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                            <select name="category_id" id="category_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                        {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Brand -->
                        <div>
                            <label for="brand" class="block text-sm font-medium text-gray-700">Brand</label>
                            <input type="text" name="brand" id="brand" 
                                   value="{{ old('brand', $product->brand ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('brand')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Barcode -->
                        <div>
                            <label for="barcode" class="block text-sm font-medium text-gray-700">Barcode</label>
                            <input type="text" name="barcode" id="barcode" 
                                   value="{{ old('barcode', $product->barcode ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('barcode')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Cost Price -->
                        <div>
                            <label for="cost_price" class="block text-sm font-medium text-gray-700">Cost Price *</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" name="cost_price" id="cost_price" 
                                       step="0.01" min="0"
                                       value="{{ old('cost_price', $product->cost_price ?? '0.00') }}"
                                       class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       required>
                            </div>
                            @error('cost_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Selling Price -->
                        <div>
                            <label for="selling_price" class="block text-sm font-medium text-gray-700">Selling Price *</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" name="selling_price" id="selling_price" 
                                       step="0.01" min="0"
                                       value="{{ old('selling_price', $product->selling_price ?? '0.00') }}"
                                       class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       required>
                            </div>
                            @error('selling_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Quantity in Stock -->
                        <div>
                            <label for="quantity_in_stock" class="block text-sm font-medium text-gray-700">Quantity in Stock *</label>
                            <input type="number" name="quantity_in_stock" id="quantity_in_stock" 
                                   min="0"
                                   value="{{ old('quantity_in_stock', $product->quantity_in_stock ?? '0') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   required>
                            @error('quantity_in_stock')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Minimum Stock Level -->
                        <div>
                            <label for="minimum_stock_level" class="block text-sm font-medium text-gray-700">Minimum Stock Level *</label>
                            <input type="number" name="minimum_stock_level" id="minimum_stock_level" 
                                   min="0"
                                   value="{{ old('minimum_stock_level', $product->minimum_stock_level ?? '0') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   required>
                            @error('minimum_stock_level')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Reorder Quantity -->
                        <div>
                            <label for="reorder_quantity" class="block text-sm font-medium text-gray-700">Reorder Quantity *</label>
                            <input type="number" name="reorder_quantity" id="reorder_quantity" 
                                   min="1"
                                   value="{{ old('reorder_quantity', $product->reorder_quantity ?? '1') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   required>
                            @error('reorder_quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Full Width Fields -->
                <div class="mt-6 space-y-6">
                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                                 class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $product->description ?? '') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Product Image</label>
                        <div class="mt-1 flex items-center">
                            <span class="inline-block h-12 w-12 overflow-hidden bg-gray-100 rounded-full">
                                @if(isset($product) && $product->image_url)
                                    <img src="{{ asset('storage/' . $product->image_url) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                @else
                                    <svg class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                @endif
                            </span>
                            <label for="image" class="ml-5 cursor-pointer">
                                <span class="rounded-md border border-gray-300 bg-white py-2 px-3 text-sm font-medium leading-4 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Change
                                </span>
                                <input id="image" name="image" type="file" class="sr-only">
                            </label>
                        </div>
                        @error('image')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tax and Status -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Taxable -->
                        <div class="flex items-start">
                            <div class="flex h-5 items-center">
                                <input id="is_taxable" name="is_taxable" type="checkbox" 
                                       class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       {{ old('is_taxable', $product->is_taxable ?? false) ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_taxable" class="font-medium text-gray-700">Taxable</label>
                                <p class="text-gray-500">Check if this product is taxable</p>
                            </div>
                        </div>

                        <!-- Active Status -->
                        <div class="flex items-start">
                            <div class="flex h-5 items-center">
                                <input id="is_active" name="is_active" type="checkbox" 
                                       class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_active" class="font-medium text-gray-700">Active</label>
                                <p class="text-gray-500">Product is available for sale</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tax Rate -->
                    <div x-data="{ isTaxable: {{ old('is_taxable', $product->is_taxable ?? false) ? 'true' : 'false' }} }"
                         x-show="isTaxable"
                         x-init="$watch('isTaxable', value => { if (!value) document.getElementById('tax_rate').value = '0.00'; })"
                         class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="tax_rate" class="block text-sm font-medium text-gray-700">Tax Rate (%)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" name="tax_rate" id="tax_rate" 
                                       x-bind:disabled="!isTaxable"
                                       step="0.01" min="0" max="100"
                                       value="{{ old('tax_rate', $product->tax_rate ?? '0.00') }}"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-50 disabled:text-gray-500"
                                       required>
                            </div>
                            @error('tax_rate')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('admin.inventory.products.index') }}" 
                       class="rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ isset($product) ? 'Update Product' : 'Add Product' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Enable/disable tax rate based on taxable checkbox
    document.addEventListener('alpine:init', () => {
        Alpine.data('taxableToggle', () => ({
            isTaxable: {{ old('is_taxable', $product->is_taxable ?? false) ? 'true' : 'false' }},
            
            toggleTaxable() {
                this.isTaxable = !this.isTaxable;
                if (!this.isTaxable) {
                    document.getElementById('tax_rate').value = '0.00';
                }
            }
        }));
    });
</script>
@endpush
@endsection
