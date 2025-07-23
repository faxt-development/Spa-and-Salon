@extends('layouts.app-content')

@section('content')
<div class="container mx-auto py-8 px-4">
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">Service Management</h1>
        <p class="text-muted-foreground">
            Select from existing services or create your own custom services for your company.
        </p>
    </div>

    <!-- Selected Services Summary -->
    <div class="bg-white rounded-lg shadow-sm border mb-8">
        <div class="p-6">
            <div class="flex items-center gap-2 mb-4">
                <h2 class="text-lg font-semibold">Your Company Services</h2>
                <span class="text-xs px-2 py-0.5 bg-primary-100 text-blue-800 rounded-full">{{ $companyServices->count() }} Active</span>
            </div>
            <link href="https://cdn.jsdelivr.net/npm/tippy.js@6.3.1/dist/tippy.css" rel="stylesheet">
            <style>
                .service-checkbox:disabled + label {
                    opacity: 0.8;
                    cursor: not-allowed;
                }
                .service-checkbox:disabled {
                    @apply bg-gray-100 border-gray-200;
                    cursor: not-allowed;
                }
                .template-indicator {
                    @apply text-xs px-2 py-0.5 bg-purple-100 text-purple-800 rounded-full;
                }
                .template-service {
                    @apply border-l-4 border-purple-500;
                }
            </style>
            <div id="selected-services-list">
                @if($companyServices->isNotEmpty())
                    @foreach($companyServices as $service)
                        @php
                            $primaryCategory = $service->categories->first();
                        @endphp
                        <div id="service-summary-{{ $service->id }}" class="mb-3 border rounded-lg {{ $service->template ? 'border-l-4 border-purple-500' : '' }}">
                            <div class="p-4">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <h3 class="font-medium">{{ $service->name }}</h3>
                                    </span>
                                    @if($service->template)
                                        <span class="text-xs px-2 py-0.5 bg-purple-100 text-purple-800 rounded-full" title="Template service">Template</span>
                                    @endif
                                    @if($primaryCategory)
                                        <span class="text-xs px-2 py-0.5 bg-primary-100 text-blue-800 rounded-full">
                                            {{ $primaryCategory->name }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 mb-2">{{ $service->description }}</p>
                                <div class="flex items-center gap-4 text-sm text-gray-500">
                                    <span>{{ $service->duration }} min</span>
                                    <span>${{ number_format($service->price, 2) }}</span>
                                    <span class="text-green-600 text-xs font-medium bg-green-50 px-2 py-0.5 rounded">Active</span>
                                    <span class="text-xs text-gray-500">Added {{ $service->pivot ? \Carbon\Carbon::parse($service->pivot->created_at)->format('n/j/Y') : '' }}</span>
                                </div>
                            </div>
                            <div class="border-t px-4 py-2 bg-gray-50 flex justify-between">
                                <div>
                                    @if(!$service->template)
                                        <a href="{{ route('admin.services.edit', $service->id) }}" class="text-blue-600 hover:text-blue-800 text-sm flex items-center mr-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                            Edit
                                        </a>
                                    @else
                                        <button type="button" class="text-blue-600 hover:text-blue-800 text-sm flex items-center" onclick="showCopyTemplateModal('{{ $service->id }}')">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M8 2a1 1 0 000 2h2a1 1 0 100-2H8z" />
                                                <path d="M3 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v6h-4.586l1.293-1.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L10.414 13H15v3a2 2 0 01-2 2H5a2 2 0 01-2-2V5zM15 11h2a1 1 0 110 2h-2v-2z" />
                                            </svg>
                                            Copy & Edit
                                        </button>
                                    @endif
                                </div>
                                @if(!$service->template)
                                <button type="button" class="text-gray-500 hover:text-red-500 text-sm flex items-center" onclick="removeService('{{ $service->id }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Remove
                                </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
                <p class="text-muted-foreground text-sm {{ $companyServices->isNotEmpty() ? 'hidden' : '' }}" id="no-services-message">
                    No services selected yet. Search and select services below.
                </p>
            </div>
            </div>

            <div class="mt-6 pt-6 border-t">
                <h3 class="text-lg font-semibold mb-4">Add More Services</h3>
                <p class="text-sm text-gray-600 mb-4">Browse and select from our catalog of available services to add to your company.</p>
                <button type="button" id="add-more-btn" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md flex items-center text-sm font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Browse Available Services
                </button>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <form method="GET" action="{{ route('admin.services') }}" class="mb-8" id="services-search-form">
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <div class="relative flex-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-3 h-4 w-4 text-muted-foreground">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input
                    type="text"
                    name="search"
                    placeholder="Search services..."
                    value="{{ $searchTerm }}"
                    class="w-full pl-10 py-2 border border-input rounded-md bg-background text-foreground"
                    id="services-search"
                />
            </div>
            <select
                name="category"
                class="px-3 py-2 border border-input rounded-md bg-background text-foreground"
                onchange="document.getElementById('services-search-form').submit()"
            >
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" {{ $selectedCategory === $category ? 'selected' : '' }}>
                        {{ $category }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>
    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Service Selection Section -->
        <div class="lg:col-span-2">
            @if($services->isEmpty())
                <div class="text-center py-8">
                    <p class="text-muted-foreground">No services found matching your criteria.</p>
                </div>
            @else
                <form method="POST" action="{{ route('services.add-to-company') }}" id="services-form">
                    @csrf
                    <div class="grid gap-4 mb-6">
                        @foreach($services as $service)
                            <div class="service-card border rounded-lg overflow-hidden {{ $service->template ? 'border-l-4 border-purple-500' : '' }}" data-template="{{ $service->template ? '1' : '0' }}">
                                <div class="p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <input type="checkbox"
                                                    id="service-{{ $service->id }}"
                                                    name="service_ids[]"
                                                    value="{{ $service->id }}"
                                                    class="service-checkbox rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500"
                                                    {{ $service->is_company_service ? 'checked' : '' }}
                                                >
                                                <label for="service-{{ $service->id }}" class="font-medium text-gray-900">
                                                    {{ $service->name }}
                                                    @if($service->template)
                                                        <span class="text-xs px-2 py-0.5 bg-purple-100 text-purple-800 rounded-full ml-2" title="Template service">Template</span>
                                                    @endif
                                                </label>
                                                @if($service->is_company_service)
                                                    <span class="text-xs px-2 py-0.5 bg-green-100 text-green-800 rounded-full">Active</span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-muted-foreground mb-2">{{ $service->description }}</p>
                                            <!-- Edit buttons removed from available services list as these are template services for selection only -->
                                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                                @php
                                                    $primaryCategory = $service->categories->first();
                                                @endphp
                                                @if($primaryCategory)
                                                    <span class="service-category px-2 py-1 text-xs font-medium rounded-full border">
                                                        {{ $primaryCategory->name }}
                                                    </span>
                                                @endif
                                                <span class="service-duration text-muted-foreground">{{ $service->duration }} min</span>
                                                <span class="service-price font-medium">${{ number_format($service->price, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </form>
            @endif
        </div>

        <!-- Actions Panel -->
        <div class="lg:col-span-1">
            <div class="sticky top-8">
                <x-card>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold flex items-center gap-2 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6 9 17l-5-5"></path>
                            </svg>
                            Actions
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-muted-foreground mb-2">
                                    Selected Services: <span id="selected-count">0</span>
                                </p>
                                <x-button type="submit" form="services-form" class="w-full">
                                    Add Selected Services
                                </x-button>
                            </div>

                            <hr class="my-4">

                            <div>
                                <p class="text-sm text-muted-foreground mb-2">
                                    Can't find what you need?
                                </p>
                                <a href="{{ route('admin.services.create') }}" class="block">
                                    <x-button type="button" class="w-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                                            <path d="M12 5v14"></path>
                                            <path d="M5 12h14"></path>
                                        </svg>
                                        Create New Service
                                    </x-button>
                                </a>
                            </div>

                            <hr class="my-4">

                            <div class="text-xs text-muted-foreground">
                                <p>💡 Tip: You can always edit or remove services later from your company dashboard.</p>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="fixed bottom-4 right-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="fixed bottom-4 right-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectedServices = new Set();
            const selectedServicesList = document.getElementById('selected-services-list');
            const noServicesMessage = document.getElementById('no-services-message');
            const addMoreBtn = document.getElementById('add-more-btn');
            const servicesSearch = document.getElementById('services-search');
            const servicesForm = document.getElementById('services-form');
            const servicesSection = document.querySelector('.lg\\:col-span-2');

            // Toggle services section visibility
            addMoreBtn.addEventListener('click', function() {
                servicesSection.classList.toggle('hidden');
                if (!servicesSection.classList.contains('hidden')) {
                    servicesSearch.focus();
                }
            });

            // Handle service selection
            function handleServiceSelection(checkbox) {
                const serviceId = checkbox.value;
                const serviceCard = checkbox.closest('.service-card');

                if (checkbox.checked) {
                    selectedServices.add(serviceId);
                    addServiceToSummary(serviceCard);

                    // Update the active services count
                    const activeServicesCount = document.querySelector('.bg-primary-100.text-blue-800.rounded-full');
                    if (activeServicesCount) {
                        const currentCount = parseInt(activeServicesCount.textContent.match(/\d+/)[0]) + 1;
                        activeServicesCount.textContent = `${currentCount} Active`;
                    }
                } else {
                    selectedServices.delete(serviceId);
                    removeServiceFromSummary(serviceId);
                }

                updateNoServicesMessage();
            }

            // Format date helper
            function formatDate(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                return `${date.getMonth() + 1}/${date.getDate()}/${date.getFullYear()}`;
            }

            // Add service to the summary section
            function addServiceToSummary(serviceCard) {
                const serviceId = serviceCard.querySelector('input[type="checkbox"]').value;
                // Get service name without the Template badge text
                const serviceNameLabel = serviceCard.querySelector('label').textContent.trim();
                const serviceName = serviceNameLabel.replace(/Template$/g, '').trim();
                const serviceCategory = serviceCard.querySelector('.service-category')?.textContent || 'Uncategorized';
                const serviceDuration = serviceCard.querySelector('.service-duration')?.textContent || '0 min';
                const servicePrice = serviceCard.querySelector('.service-price')?.textContent || '$0.00';
                const serviceDescription = serviceCard.querySelector('.service-description')?.textContent || '';

                // Check if service is already in the summary
                if (document.getElementById(`service-summary-${serviceId}`)) return;

                // Check if this is a template service
                const serviceIsTemplate = serviceCard.dataset.template === '1';
                const currentDate = new Date();
                const formattedDate = formatDate(currentDate);

                // Create the service summary HTML
                let serviceHtml = `
                    <div id="service-summary-${serviceId}" class="mb-3 border rounded-lg ${serviceIsTemplate ? 'border-l-4 border-purple-500' : ''}">
                        <div class="p-4">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="inline-flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <h3 class="font-medium">${serviceName}</h3>
                                </span>
                                ${serviceIsTemplate ? '<span class="text-xs px-2 py-0.5 bg-purple-100 text-purple-800 rounded-full" title="Template service">Template</span>' : ''}
                                <span class="text-xs px-2 py-0.5 bg-primary-100 text-blue-800 rounded-full">${serviceCategory}</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">${serviceDescription}</p>
                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                <span>${serviceDuration}</span>
                                <span>${servicePrice}</span>
                                <span class="text-green-600 text-xs font-medium bg-green-50 px-2 py-0.5 rounded">Active</span>
                                <span class="text-xs text-gray-500">Added ${formattedDate}</span>
                            </div>
                        </div>`;

                // Add remove button if not a template service
                if (!serviceIsTemplate) {
                    serviceHtml += `
                        <div class="border-t px-4 py-2 bg-gray-50 flex justify-end">
                            <button type="button" class="text-gray-500 hover:text-red-500 text-sm flex items-center" onclick="removeService('${serviceId}')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                Remove
                            </button>
                        </div>`;
                }

                // Close the div
                serviceHtml += '</div>';

                // Insert into the selected services list
                if (noServicesMessage) {
                    noServicesMessage.classList.add('hidden');
                }
                selectedServicesList.insertAdjacentHTML('beforeend', serviceHtml);
            }

            // Remove service from summary
            function removeServiceFromSummary(serviceId) {
                const serviceElement = document.getElementById(`service-summary-${serviceId}`);
                if (serviceElement) {
                    serviceElement.remove();
                }

                // Update the active services count
                const activeServicesCount = document.querySelector('.bg-primary-100.text-blue-800.rounded-full');
                if (activeServicesCount) {
                    const currentCount = parseInt(activeServicesCount.textContent.match(/\d+/)[0]) - 1;
                    activeServicesCount.textContent = `${currentCount} Active`;
                }

                // Uncheck the corresponding checkbox
                const checkbox = document.querySelector(`input[type="checkbox"][value="${serviceId}"]`);
                if (checkbox) {
                    checkbox.checked = false;
                }

                selectedServices.delete(serviceId);
                updateNoServicesMessage();
            }

            // Update the "No services" message visibility
            function updateNoServicesMessage() {
                noServicesMessage.style.display = selectedServices.size === 0 ? 'block' : 'none';
            }

            // Function to show confirmation dialog
            function showConfirmationDialog(message) {
                return new Promise((resolve) => {
                    // Create dialog container
                    const dialog = document.createElement('div');
                    dialog.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                    dialog.innerHTML = `
                        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                            <h3 class="text-lg font-medium mb-4">Confirm Removal</h3>
                            <p class="text-gray-700 mb-6">${message}</p>
                            <div class="flex justify-end space-x-3">
                                <button type="button" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" id="confirm-cancel">
                                    Cancel
                                </button>
                                <button type="button" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" id="confirm-ok">
                                    Remove
                                </button>
                            </div>
                        </div>
                    `;

                    // Add to body
                    document.body.appendChild(dialog);
                    document.body.style.overflow = 'hidden';

                    // Return a promise that resolves when the user makes a choice
                    return new Promise((resolve) => {
                        dialog.querySelector('#confirm-ok').addEventListener('click', () => {
                            document.body.removeChild(dialog);
                            document.body.style.overflow = '';
                            resolve(true);
                        });

                        dialog.querySelector('#confirm-cancel').addEventListener('click', () => {
                            document.body.removeChild(dialog);
                            document.body.style.overflow = '';
                            resolve(false);
                        });
                    });
                });
            }

            // Function to show toast messages
            function showToast(message, type = 'info') {
                const toast = document.createElement('div');
                const colors = {
                    success: 'bg-green-100 border-green-500 text-green-700',
                    error: 'bg-red-100 border-red-500 text-red-700',
                    info: 'bg-primary-100 border-blue-500 text-blue-700'
                };

                toast.className = `fixed top-4 right-4 border-l-4 p-4 rounded shadow-lg ${colors[type] || colors.info} transition-all duration-300 transform translate-x-0`;
                toast.innerHTML = `
                    <div class="flex items-center">
                        <span class="mr-2">
                            ${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}
                        </span>
                        <span>${message}</span>
                        <button class="ml-4 text-gray-500 hover:text-gray-700" onclick="this.parentElement.parentElement.remove()">
                            &times;
                        </button>
                    </div>
                `;

                document.body.appendChild(toast);

                // Auto-remove after 5 seconds
                setTimeout(() => {
                    toast.style.transform = 'translateX(120%)';
                    setTimeout(() => {
                        if (toast.parentNode) {
                            document.body.removeChild(toast);
                        }
                    }, 300);
                }, 5000);
            }

            // Function to remove a service from the company
            async function removeService(serviceId) {
                // Get service name for confirmation message
                const serviceElement = document.getElementById(`service-summary-${serviceId}`);
                if (!serviceElement) return;

                const serviceName = serviceElement.querySelector('h3')?.textContent?.trim() || 'this service';

                // Show confirmation dialog
                const confirmed = await showConfirmationDialog(`Are you sure you want to remove "${serviceName}" from your company's services?`);

                if (!confirmed) {
                    return; // User cancelled
                }

                try {
                    const response = await fetch(`/admin/services/${serviceId}/remove-from-company`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Failed to remove service');
                    }

                    if (data.success) {
                        // Remove the service from the UI
                        if (serviceElement) {
                            serviceElement.style.transition = 'opacity 0.3s ease';
                            serviceElement.style.opacity = '0';
                            setTimeout(() => {
                                serviceElement.remove();
                            }, 300);
                        }

                        // Uncheck the corresponding checkbox
                        const checkbox = document.querySelector(`input[type="checkbox"][value="${serviceId}"]`);
                        if (checkbox) {
                            checkbox.checked = false;
                            // Trigger change event to update the UI
                            const event = new Event('change');
                            checkbox.dispatchEvent(event);
                        }

                        // Update the selected services set
                        selectedServices.delete(serviceId.toString());
                        updateNoServicesMessage();

                        // Show success message
                        showToast('Service removed successfully', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to remove service');
                    }
                } catch (error) {
                    console.error('Error removing service:', error);
                    showToast(error.message || 'An error occurred while removing the service', 'error');
                }
            }

            // Make removeService available globally for onclick handlers
            window.removeService = removeService;

            // Function to initialize selected services
            function initializeSelectedServices() {
                // Get all company service IDs from the server-rendered HTML
                const companyServiceIds = @json($companyServices->pluck('id')->toArray());

                // Check the checkboxes for company services
                companyServiceIds.forEach(serviceId => {
                    const checkbox = document.querySelector(`input[type="checkbox"][value="${serviceId}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                        // Add to selected services set
                        selectedServices.add(serviceId.toString());
                    }
                });

                // Update the UI to show selected services count
                updateNoServicesMessage();
            }

            // Initialize event listeners for checkboxes
            document.addEventListener('change', function(e) {
                if (e.target.matches('input[type="checkbox"][name^="service_ids"]')) {
                    handleServiceSelection(e.target);
                }
            });

            // Add tooltips for template services
            tippy('[data-tippy-content]');

            // Initialize the page with selected services
            initializeSelectedServices();

        });

        // Template Service Copy Modal Functions
        function showCopyTemplateModal(serviceId) {
            const modal = document.getElementById('copy-template-modal');
            const confirmBtn = modal.querySelector('.confirm-copy-btn');

            // Set service ID for the confirm button
            confirmBtn.dataset.serviceId = serviceId;

            // Show the modal
            modal.classList.remove('hidden');
        }

        function copyTemplateService(serviceId) {
            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const companyId = {{ $company->id }};

            fetch(`/admin/services/companies/${companyId}/services/${serviceId}/copy`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Template service copied successfully. You can now edit the copy.', 'success');
                    // Redirect to edit page for the new service
                    window.location.href = `/admin/services/${data.service.id}/edit`;
                } else {
                    showToast(data.message || 'Error copying template service', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while copying the template service', 'error');
            });
        }
    </script>
    @endpush

    <!-- Template Service Copy Modal -->
    <div id="copy-template-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-semibold mb-4">Copy Template Service</h3>
            <p class="mb-4">Template services cannot be edited directly. Would you like to create a copy of this service that you can customize?</p>
            <div class="flex justify-end space-x-2">
                <button type="button" class="px-4 py-2 bg-gray-200 rounded-md cancel-btn" onclick="document.getElementById('copy-template-modal').classList.add('hidden')">Cancel</button>
                <button type="button" class="px-4 py-2 bg-primary-600 text-white rounded-md confirm-copy-btn" onclick="copyTemplateService(this.dataset.serviceId); document.getElementById('copy-template-modal').classList.add('hidden');">Create Copy</button>
            </div>
        </div>
    </div>
</div>
@endsection
