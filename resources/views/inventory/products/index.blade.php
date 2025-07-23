@extends('layouts.app-content')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Products</h1>
            <p class="text-gray-600">Manage your products</p>
        </div>
        <div class="mt-4 md:mt-0">
            <button @click="showCategoryForm = true"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Add Category
            </button>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg" x-data="{
        selected: [],
        selectAll: false,
        bulkAction: '',

        init() {
            // Initialize any bulk action functionality here
            this.$watch('selectAll', value => {
                this.selected = value ? @json($products->pluck('id')->toArray()) : [];
            });
        },

        get selectedCount() {
            return this.selected.length;
        },

        isSelected(categoryId) {
            return this.selected.includes(categoryId);
        },

        toggleCategory(categoryId) {
            const index = this.selected.indexOf(categoryId);
            if (index === -1) {
                this.selected.push(categoryId);
            } else {
                this.selected.splice(index, 1);
            }
            // Update select all checkbox
            this.selectAll = this.selected.length === @json($products->count());
        },


    }">
        <!-- Bulk Actions Bar -->
        <div x-show="selectedCount > 0"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="bg-primary-50 border-b border-blue-200 px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <span class="text-sm font-medium text-blue-800">
                        <span x-text="selectedCount"></span> selected
                    </span>
                </div>
                <div class="flex space-x-2">
                    <select x-model="bulkAction"
                            @change="executeBulkAction()"
                            class="block w-40 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">Bulk Actions</option>
                        <option value="activate">Activate</option>
                        <option value="deactivate">Deactivate</option>
                        <option value="delete">Delete</option>
                    </select>
                    <button @click="selected = []; selectAll = false;"
                            class="ml-2 inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Clear
                    </button>
                </div>
            </div>
        </div>

        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                <div class="w-full sm:w-auto mb-4 sm:mb-0">
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text"
                               x-model="searchQuery"
                               @keyup.debounce.500ms="filter"
                               class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md"
                               placeholder="Search ...">
                    </div>
                </div>
            </div>
        </div>

        @if ($products->isEmpty())
            <div class="px-4 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No  found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new category.</p>
                <div class="mt-6">
                    <button @click="showCategoryForm = true"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        New Category
                    </button>
                </div>
            </div>
        @else
            <div class="bg-white shadow overflow-hidden sm:rounded-b-lg">
                <ul class="divide-y divide-gray-200">
                    @foreach ($products as $product)
                                    <div class="flex items-start">
                                        @if($product->image_path)
                                            <div class="flex-shrink-0 h-16 w-16 mr-4">
                                                <img class="h-16 w-16 rounded-md object-cover"
                                                     src="{{ asset('storage/' . $product->image_path) }}"
                                                     alt="{{ $product->name }}">
                                            </div>
                                        @endif
                                        <div>
                                            <p class="text-sm font-medium text-blue-600">
                                                {{ $product->name }}
                                            </p>
                                            @if($product->parent_id)
                                                <span class="mt-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                                    </svg>
                                                    {{ $product->parent->name ?? 'Parent Category' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-primary-100 text-blue-800">
                                        {{ $product->products_count }} {{ Str::plural('product', $product->products_count) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span x-data="{ status: {{ $product->is_active ? 'true' : 'false' }} }"
                                          @click.self="status = !status;
                                              fetch('{{ route('inventory..update', $product) }}', {
                                                  method: 'POST',
                                                  headers: {
                                                      'Content-Type': 'application/json',
                                                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                      'X-Requested-With': 'XMLHttpRequest',
                                                      'X-HTTP-Method-Override': 'PATCH'
                                                  },
                                                  body: JSON.stringify({
                                                      is_active: status,
                                                      _method: 'PATCH'
                                                  })
                                              })"
                                          :class="{
                                              'bg-green-100 text-green-800': status,
                                              'bg-red-100 text-red-800': !status
                                          }"
                                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full cursor-pointer">
                                        <span x-text="status ? 'Active' : 'Inactive'"></span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="editCategory({{ json_encode($product) }})"
                                        class="mr-2 bg-white text-gray-400 hover:text-gray-500 focus:outline-none">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button @click="confirmDelete({{ $product->id }})"
                                        class="bg-white text-red-400 hover:text-red-500 focus:outline-none">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($products->hasPages())
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $products->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Category Form Modal -->
<div x-show="showCategoryForm"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed z-10 inset-0 overflow-y-auto"
     aria-labelledby="modal-title"
     x-ref="dialog"
     aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showCategoryForm"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
             @click="showCategoryForm = false"
             aria-hidden="true"></div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="showCategoryForm"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-primary-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        <span x-text="editing ? 'Edit Category' : 'New Category'"></span>
                    </h3>
                    <div class="mt-4">
                        <form id="categoryForm" @submit.prevent="saveCategory">
                            @csrf
                            <input type="hidden" name="_method" x-model="formData._method">

                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       x-model="formData.name"
                                       required
                                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div class="mb-4">
                                <label for="parent_id" class="block text-sm font-medium text-gray-700">Parent Category (Optional)</label>
                                <select id="parent_id"
                                        name="parent_id"
                                        x-model="formData.parent_id"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                    <option value="">-- Select a parent category --</option>
                                    @foreach($products as $parent)
                                        <option value="{{ $parent->id }}" {{ $parent->parent_id ? 'disabled' : '' }}>
                                            {{ $parent->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                                <textarea id="description"
                                          name="description"
                                          x-model="formData.description"
                                          rows="3"
                                          class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border border-gray-300 rounded-md"></textarea>
                            </div>

                            <div class="flex items-center mb-4">
                                <input id="is_active"
                                       name="is_active"
                                       type="checkbox"
                                       x-model="formData.is_active"
                                       class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                    Active
                                </label>
                            </div>

                            <!-- Image Upload -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Category Image
                                </label>

                                <!-- Image Preview -->
                                <div x-show="formData.image_preview || formData.existing_image" class="mb-4">
                                    <img :src="formData.image_preview || '/storage/' + formData.existing_image"
                                         alt="Category preview"
                                         class="h-32 w-32 object-cover rounded-md">

                                    <button type="button"
                                            @click="removeImage()"
                                            class="mt-2 inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Remove Image
                                    </button>

                                    <input type="hidden" name="remove_image" x-model="formData.remove_image">
                                </div>

                                <!-- File Input -->
                                <div x-show="!formData.image_preview && !formData.existing_image"
                                     class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                <span>Upload a file</span>
                                                <input id="image"
                                                       name="image"
                                                       type="file"
                                                       class="sr-only"
                                                       @change="handleImageUpload($event)">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            PNG, JPG, GIF up to 2MB
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button type="button"
                        @click="saveCategory"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    <span x-text="editing ? 'Update' : 'Create'"></span>
                </button>
                <button type="button"
                        @click="showCategoryForm = false"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Cancel
                </button>
                <button x-show="editing"
                        type="button"
                        @click="deleteCategory"
                        class="mr-auto inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div x-show="showDeleteModal"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed z-10 inset-0 overflow-y-auto"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showDeleteModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
             @click="showDeleteModal = false"
             aria-hidden="true"></div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="showDeleteModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Delete Category
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to delete this category? This action cannot be undone.
                        </p>
                        <p x-show="categoryProductsCount > 0" class="mt-2 text-sm text-red-600">
                            This category contains <span x-text="categoryProductsCount"></span> products. These products will be moved to "Uncategorized".
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <form id="deleteForm" method="POST" :action="deleteUrl">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                </form>
                <button type="button"
                        @click="showDeleteModal = false"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('', () => ({
            showCategoryForm: false,
            showDeleteModal: false,
            editing: false,
            searchQuery: '',
            categoryId: null,
            categoryProductsCount: 0,
            deleteUrl: '',
            formData: {
                _method: 'POST',
                name: '',
                parent_id: '',
                description: '',
                is_active: true,
                image: null,
                image_preview: null,
                existing_image: null,
                remove_image: false
            },

            init() {
                // Initialize any Alpine.js functionality here
                this.$watch('searchQuery', (value) => {
                    this.filter();
                });

                // Listen for success messages
                @if(session('success'))
                    this.showNotification('{{ session('success') }}', 'success');
                @endif

                @if($errors->any())
                    this.showNotification('{{ $errors->first() }}', 'error');
                @endif
            },

            filter() {
                const url = new URL(window.location.href);
                if (this.searchQuery) {
                    url.searchParams.set('search', this.searchQuery);
                } else {
                    url.searchParams.delete('search');
                }
                window.location = url.toString();
            },

            newCategory() {
                this.editing = false;
                this.formData = {
                    _method: 'POST',
                    name: '',
                    parent_id: '',
                    description: '',
                    is_active: true
                };
                this.showCategoryForm = true;
            },

            editCategory(category) {
                this.editing = true;
                this.categoryId = category.id;
                this.formData = {
                    _method: 'PUT',
                    name: category.name,
                    parent_id: category.parent_id || '',
                    description: category.description || '',
                    is_active: category.is_active
                };
                this.showCategoryForm = true;
            },

            saveCategory() {
                const form = document.getElementById('categoryForm');
                const formData = new FormData(form);

                // Add the is_active value to the form data
                formData.append('is_active', this.formData.is_active ? 1 : 0);

                // If we have a new image, append it
                if (this.formData.image) {
                    formData.append('image', this.formData.image);
                }

                // If we're removing an existing image
                if (this.formData.remove_image) {
                    formData.append('remove_image', '1');
                }

                const url = this.editing
                    ? `/inventory//${this.categoryId}`
                    : '/inventory/';

                fetch(url, {
                    method: this.editing ? 'POST' : 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw err; });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect;
                    } else {
                        this.showNotification(data.message || 'An error occurred', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const errorMessage = error.message || 'An error occurred while saving the category';
                    this.showNotification(errorMessage, 'error');
                });
            },

            confirmDelete(categoryId) {
                this.categoryId = categoryId;
                this.deleteUrl = `/inventory//${categoryId}`;

                // Fetch product count for this category
                fetch(`/api//${categoryId}/products/count`)
                    .then(response => response.json())
                    .then(data => {
                        this.categoryProductsCount = data.count || 0;
                        this.showDeleteModal = true;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.categoryProductsCount = 0;
                        this.showDeleteModal = true;
                    });
            },

            deleteCategory() {
                const form = document.getElementById('deleteForm');
                form.submit();
            },

            showNotification(message, type = 'success') {
                // You can implement a toast notification here
                // For now, we'll just use a simple alert
                alert(`${type.toUpperCase()}: ${message}`);
            }
        }));
    });
</script>
@endpush

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
