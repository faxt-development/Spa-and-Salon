@extends('layouts.app')

@section('content')
<div x-data="promotionForm()" class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-semibold text-gray-900">
                    {{ isset($promotion->id) ? 'Edit Promotion' : 'Create Promotion' }}
                </h2>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('promotions.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button 
                    @click="save()" 
                    type="button" 
                    class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    :disabled="saving">
                    <svg x-show="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="saving ? 'Saving...' : 'Save'"></span>
                </button>
            </div>
        </div>

        <form id="promotion-form" method="POST" action="{{ isset($promotion->id) ? route('promotions.update', $promotion) : route('promotions.store') }}" enctype="multipart/form-data">
            @csrf
            @if(isset($promotion->id))
                @method('PUT')
            @endif

            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <!-- Basic Information -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Basic Information</h3>
                            <p class="mt-1 text-sm text-gray-500">General details about the promotion.</p>
                        </div>

                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <!-- Name -->
                            <div class="sm:col-span-4">
                                <label for="name" class="block text-sm font-medium text-gray-700">Promotion Name</label>
                                <div class="mt-1">
                                    <input type="text" name="name" id="name" x-model="form.name" required
                                           class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <p class="mt-1 text-sm text-red-600" x-show="errors.name" x-text="errors.name[0]"></p>
                            </div>

                            <!-- Code -->
                            <div class="sm:col-span-2">
                                <label for="code" class="block text-sm font-medium text-gray-700">Promo Code</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="text" name="code" id="code" x-model="form.code" required
                                           class="flex-1 min-w-0 block w-full px-3 py-2 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300"
                                           :class="{'bg-gray-100': !form.is_public}" 
                                           :disabled="!form.is_public"
                                           @input="form.code = form.code.toUpperCase()">
                                    <button type="button" 
                                            @click="generateCode()"
                                            class="ml-3 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Generate
                                    </button>
                                </div>
                                <p class="mt-1 text-sm text-red-600" x-show="errors.code" x-text="errors.code[0]"></p>
                            </div>

                            <!-- Description -->
                            <div class="sm:col-span-6">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <div class="mt-1">
                                    <textarea id="description" name="description" rows="3" x-model="form.description"
                                              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 rounded-md"></textarea>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">A brief description of the promotion that will be shown to customers.</p>
                            </div>

                            <!-- Type -->
                            <div class="sm:col-span-3">
                                <label for="type" class="block text-sm font-medium text-gray-700">Promotion Type</label>
                                <select id="type" name="type" x-model="form.type" 
                                        @change="updateFormForType()"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    @foreach($types as $type => $details)
                                        <option value="{{ $type }}">{{ $details['name'] }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-500" x-text="types[form.type].description"></p>
                                <p class="mt-1 text-sm text-red-600" x-show="errors.type" x-text="errors.type[0]"></p>
                            </div>

                            <!-- Value -->
                            <div class="sm:col-span-3">
                                <label for="value" class="block text-sm font-medium text-gray-700">
                                    <span x-text="types[form.type].value_label"></span>
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div x-show="!['bogo', 'package'].includes(form.type)" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span x-show="form.type === 'percentage'" class="text-gray-500 sm:text-sm">%</span>
                                        <span x-show="form.type === 'fixed'" class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" name="value" id="value" x-model="form.value"
                                           :step="form.type === 'percentage' ? '0.01' : '0.01'"
                                           :min="form.type === 'percentage' ? '0.01' : '0.01'"
                                           :max="form.type === 'percentage' ? '100' : ''"
                                           :required="!['bogo', 'package'].includes(form.type)"
                                           :disabled="['bogo', 'package'].includes(form.type)"
                                           class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md"
                                           :class="{'pl-7': !['bogo', 'package'].includes(form.type), 'bg-gray-100': ['bogo', 'package'].includes(form.type)}">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span x-show="form.type === 'percentage'" class="text-gray-500 sm:text-sm" id="price-currency">%</span>
                                        <span x-show="form.type === 'fixed'" class="text-gray-500 sm:text-sm" id="price-currency">USD</span>
                                    </div>
                                </div>
                                <p class="mt-1 text-sm text-red-600" x-show="errors.value" x-text="errors.value[0]"></p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <!-- Status -->
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="is_active" class="block text-sm font-medium text-gray-700">Status</label>
                                <div class="mt-1">
                                    <div class="flex items-center">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" id="is_active" name="is_active" value="1" 
                                               x-model="form.is_active"
                                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                            Active
                                        </label>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">Inactive promotions will not be available for use.</p>
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="is_public" class="block text-sm font-medium text-gray-700">Visibility</label>
                                <div class="mt-1">
                                    <div class="flex items-center">
                                        <input type="hidden" name="is_public" value="0">
                                        <input type="checkbox" id="is_public" name="is_public" value="1" 
                                               x-model="form.is_public"
                                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label for="is_public" class="ml-2 block text-sm text-gray-700">
                                            Public
                                        </label>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">Public promotions can be used by anyone with the code.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <!-- Date Range -->
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="starts_at" class="block text-sm font-medium text-gray-700">Start Date & Time</label>
                                <div class="mt-1">
                                    <input type="datetime-local" name="starts_at" id="starts_at" 
                                           x-model="form.starts_at"
                                           class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <p class="mt-1 text-sm text-red-600" x-show="errors.starts_at" x-text="errors.starts_at[0]"></p>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="ends_at" class="block text-sm font-medium text-gray-700">End Date & Time (Optional)</label>
                                <div class="mt-1">
                                    <input type="datetime-local" name="ends_at" id="ends_at" 
                                           x-model="form.ends_at"
                                           class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <p class="mt-1 text-sm text-red-600" x-show="errors.ends_at" x-text="errors.ends_at[0]"></p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <!-- Usage Limits -->
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="usage_limit" class="block text-sm font-medium text-gray-700">Usage Limit (Optional)</label>
                                <div class="mt-1">
                                    <input type="number" name="usage_limit" id="usage_limit" 
                                           x-model="form.usage_limit"
                                           min="1"
                                           class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Maximum number of times this promotion can be used. Leave blank for unlimited.</p>
                                <p class="mt-1 text-sm text-red-600" x-show="errors.usage_limit" x-text="errors.usage_limit[0]"></p>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="min_requirements" class="block text-sm font-medium text-gray-700">Minimum Requirements (Optional)</label>
                                <div class="mt-1">
                                    <input type="number" name="min_requirements" id="min_requirements" 
                                           x-model="form.min_requirements"
                                           min="0" step="0.01"
                                           class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Minimum cart total required for this promotion to apply.</p>
                                <p class="mt-1 text-sm text-red-600" x-show="errors.min_requirements" x-text="errors.min_requirements[0]"></p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-8 border-t border-gray-200" x-show="form.type === 'package' || form.type === 'bundle'">
                        <!-- Package/Bundle Items -->
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="sm:col-span-6">
                                <label class="block text-sm font-medium text-gray-700">Included Services</label>
                                <p class="mt-1 text-sm text-gray-500">Select services included in this package.</p>
                                <div class="mt-2 space-y-2">
                                    @foreach($services as $service)
                                        <div class="flex items-center">
                                            <input type="checkbox" 
                                                   id="service_{{ $service->id }}" 
                                                   name="services[]" 
                                                   value="{{ $service->id }}"
                                                   x-model="form.services"
                                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label for="service_{{ $service->id }}" class="ml-2 block text-sm text-gray-700">
                                                {{ $service->name }} - ${{ number_format($service->price, 2) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="mt-1 text-sm text-red-600" x-show="errors.services" x-text="errors.services[0]"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function promotionForm() {
        return {
            form: {
                name: '{{ old('name', $promotion->name ?? '') }}',
                code: '{{ old('code', $promotion->code ?? '') }}',
                description: '{{ old('description', $promotion->description ?? '') }}',
                type: '{{ old('type', $promotion->type ?? 'percentage') }}',
                value: '{{ old('value', $promotion->value ?? '10') }}',
                is_active: {{ old('is_active', isset($promotion) ? $promotion->is_active : '1') }},
                is_public: {{ old('is_public', $promotion->is_public ?? '1') }},
                starts_at: '{{ old('starts_at', isset($promotion->starts_at) ? $promotion->starts_at->format('Y-m-d\TH:i') : '') }}',
                ends_at: '{{ old('ends_at', isset($promotion->ends_at) ? $promotion->ends_at->format('Y-m-d\TH:i') : '') }}',
                usage_limit: '{{ old('usage_limit', $promotion->usage_limit ?? '') }}',
                min_requirements: '{{ old('min_requirements', $promotion->min_requirements ?? '') }}',
                services: @json(old('services', $selectedServices ?? [])),
            },
            errors: @json($errors->toArray()),
            saving: false,
            types: @json($types),
            
            init() {
                // Set default values for BOGO and Package types
                if (['bogo', 'package'].includes(this.form.type)) {
                    this.form.value = this.types[this.form.type].default_value;
                }
                
                // Set up form submission handling
                this.$watch('form.type', (newType) => {
                    this.updateFormForType();
                });
            },
            
            updateFormForType() {
                // Update form values based on type
                if (['bogo', 'package'].includes(this.form.type)) {
                    this.form.value = this.types[this.form.type].default_value;
                }
            },
            
            generateCode() {
                // Generate a random 8-character alphanumeric code
                const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                let result = '';
                for (let i = 0; i < 8; i++) {
                    result += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                this.form.code = result;
            },
            
            save() {
                this.saving = true;
                document.getElementById('promotion-form').submit();
            },
            
            formatDate(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                return date.toLocaleString();
            },
            
            formatCurrency(amount) {
                if (amount === null || amount === '') return '';
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    minimumFractionDigits: 2
                }).format(amount);
            },
            
            formatPercentage(amount) {
                if (amount === null || amount === '') return '';
                return `${parseFloat(amount).toFixed(2)}%`;
            }
        };
    }
</script>
@endpush

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush
@endsection
