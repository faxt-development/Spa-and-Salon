@extends('layouts.app-content')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Service Categories</h1>
        <div>
            <button id="add-template-categories" class="bg-secondary-600 hover:bg-secondary-700 text-white font-bold py-2 px-4 rounded mr-2">
                Add Template Categories
            </button>
            <a href="{{ route('admin.services.categories.create') }}" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded">
                Create New Category
            </a>
        </div>
    </div>

    <!-- User guidance panel -->
    <div class="bg-primary-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert">
        <h3 class="font-bold text-lg">How Service Categories Work</h3>
        <p class="mb-2">There are two types of service categories:</p>
        <ul class="list-disc ml-6 mb-2">
            <li><strong>Template Categories</strong> - Pre-defined categories that cannot be edited or deleted. You can add these to your business or make a copy to customize.</li>
            <li><strong>Custom Categories</strong> - Categories you create or customize for your business. These can be edited and deleted.</li>
        </ul>
        <p><strong>To use a template category:</strong> Click "Copy to My Categories" to create your own editable version, or use the "Add Template Categories" button to add multiple templates at once.</p>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="p-4 border-b">
            <form action="{{ route('admin.services.categories') }}" method="GET" class="space-y-4">
                <div class="flex items-center">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search categories..."
                        class="form-input rounded-md shadow-sm mr-2 w-full md:w-1/3">
                    <button type="submit"
                        class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded">
                        Search
                    </button>
                    @if($search)
                        <a href="{{ route('admin.services.categories') }}"
                            class="ml-2 text-gray-600 hover:text-gray-800">
                            Clear
                        </a>
                    @endif
                </div>

                <div class="flex items-center space-x-4">
                    <span class="text-sm font-medium text-gray-700">Show:</span>
                    <div class="flex space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="show_associated" value="all" {{ ($showAssociated ?? 'all') == 'all' ? 'checked' : '' }}
                                class="form-radio h-4 w-4 text-primary-600" onchange="this.form.submit()">
                            <span class="ml-2 text-sm text-gray-700">All Categories</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="show_associated" value="company_only" {{ ($showAssociated ?? '') == 'company_only' ? 'checked' : '' }}
                                class="form-radio h-4 w-4 text-primary-600" onchange="this.form.submit()">
                            <span class="ml-2 text-sm text-gray-700">My Categories</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="show_associated" value="templates_only" {{ ($showAssociated ?? '') == 'templates_only' ? 'checked' : '' }}
                                class="form-radio h-4 w-4 text-primary-600" onchange="this.form.submit()">
                            <span class="ml-2 text-sm text-gray-700">Template Categories</span>
                        </label>
                    </div>
                </div>
            </form>
        </div>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Order
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Name
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Description
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Type
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($categories as $category)
                    <tr class="{{ $category->template ? 'bg-gray-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $category->display_order }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $category->name }}</div>
                            <div class="text-sm text-gray-500">{{ $category->slug }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ Str::limit($category->description, 100) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($category->active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($category->template)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-primary-100 text-blue-800">
                                    Template
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Custom
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if($category->template)
                                <!-- Template categories can be copied but not edited/deleted -->
                                <button
                                    class="copy-template-btn bg-primary-100 hover:bg-primary-200 text-blue-800 font-medium py-1 px-3 rounded-md flex items-center"
                                    data-category-id="{{ $category->id }}"
                                    data-company-id="{{ $company ? $company->id : '' }}"
                                    {{ !$company ? 'disabled' : '' }}
                                    title="Create an editable copy of this template category for your business"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Copy to My Categories
                                </button>
                            @else
                                <!-- Custom categories can be edited and deleted -->
                                <a href="{{ route('admin.services.categories.edit', $category) }}" class="text-primary-600 hover:text-primary-900 mr-3">Edit</a>

                                <form action="{{ route('admin.services.categories.destroy', $category) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this category?')">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            No service categories found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $categories->links() }}
    </div>

    <!-- Template Categories Modal -->
    <div id="template-categories-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-3/4 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center border-b pb-3">
                    <h3 class="text-xl font-medium text-gray-900">Add Template Categories to Your Business</h3>
                    <button type="button" id="close-modal-x" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-4 px-7 py-3">
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>How this works:</strong> Select the template categories you want to use in your business. These will be added to your business but remain as templates (non-editable). If you need to customize a category, use the "Copy to My Categories" button on the main page instead.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form id="add-template-categories-form" action="{{ route('admin.services.categories.add-to-company') }}" method="POST">
                        @csrf
                        <div class="max-h-96 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Select
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Description
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($categories as $category)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input
                                                    type="checkbox"
                                                    name="category_ids[]"
                                                    value="{{ $category->id }}"
                                                    {{ $category->is_company_category ? 'checked disabled' : '' }}
                                                    {{ in_array($category->id, $selectedCategories ?? []) ? 'checked' : '' }}
                                                    class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                                                    id="category-{{ $category->id }}"
                                                >
                                                @if($category->is_company_category)
                                                <span class="ml-2 text-xs text-green-600 font-medium">Already added</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $category->name }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">{{ Str::limit($category->description, 100) }}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-between items-center border-t pt-4">
                            <div>
                                <button type="button" id="select-all-categories" class="text-primary-600 hover:text-primary-800 text-sm font-medium">
                                    Select All Available
                                </button>
                                <span class="mx-2 text-gray-400">|</span>
                                <button type="button" id="deselect-all-categories" class="text-primary-600 hover:text-primary-800 text-sm font-medium">
                                    Deselect All
                                </button>
                            </div>
                            <div class="flex">
                                <button type="button" id="close-modal" class="mr-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    Cancel
                                </button>
                                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Selected Categories
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal handling
        const modal = document.getElementById('template-categories-modal');
        const openModalBtn = document.getElementById('add-template-categories');
        const closeModalBtn = document.getElementById('close-modal');
        const closeModalXBtn = document.getElementById('close-modal-x');
        const selectAllBtn = document.getElementById('select-all-categories');
        const deselectAllBtn = document.getElementById('deselect-all-categories');

        openModalBtn.addEventListener('click', function() {
            modal.classList.remove('hidden');
        });

        closeModalBtn.addEventListener('click', function() {
            modal.classList.add('hidden');
        });

        closeModalXBtn.addEventListener('click', function() {
            modal.classList.add('hidden');
        });

        // Select/deselect all categories functionality
        selectAllBtn.addEventListener('click', function() {
            document.querySelectorAll('input[name="category_ids[]"]').forEach(checkbox => {
                if (!checkbox.disabled) {
                    checkbox.checked = true;
                }
            });
        });

        deselectAllBtn.addEventListener('click', function() {
            document.querySelectorAll('input[name="category_ids[]"]').forEach(checkbox => {
                if (!checkbox.disabled) {
                    checkbox.checked = false;
                }
            });
        });

        // Copy template category functionality
        document.querySelectorAll('.copy-template-btn').forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-category-id');
                const companyId = this.getAttribute('data-company-id');

                if (!companyId) {
                    alert('No company associated with this user.');
                    return;
                }

                // Send AJAX request to copy the template
                fetch(`/admin/services/categories/${companyId}/copy-template/${categoryId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while copying the template category.');
                });
            });
        });

        // Remove category from company functionality
        document.querySelectorAll('.remove-category-btn').forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-category-id');

                if (confirm('Are you sure you want to remove this category from your company?')) {
                    // Send AJAX request to remove the category
                    fetch(`/admin/services/categories/remove-from-company/${categoryId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while removing the category.');
                    });
                }
            });
        });
    });
</script>
@endpush
@endsection
