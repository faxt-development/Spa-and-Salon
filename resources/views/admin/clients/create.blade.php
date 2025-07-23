<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add New Client') }}
            </h2>
            <a href="{{ route('admin.clients.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                {{ __('Back to Clients') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Client Form using Alpine.js -->
                    <div x-data="clientForm()">
                        <form method="POST" action="{{ route('admin.clients.store') }}" @submit.prevent="submitForm">
                            @csrf

                            <!-- Success Message -->
                            <div x-show="successMessage" x-transition class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                                <span x-text="successMessage"></span>
                            </div>

                            <!-- Error Message -->
                            <div x-show="errorMessage" x-transition class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                <span x-text="errorMessage"></span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- First Name -->
                                <div>
                                    <x-label for="first_name" :value="__('First Name')" />
                                    <x-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" x-model="form.first_name" required />
                                    <div x-show="errors.first_name" class="text-red-500 text-sm mt-1" x-text="errors.first_name"></div>
                                </div>

                                <!-- Last Name -->
                                <div>
                                    <x-label for="last_name" :value="__('Last Name')" />
                                    <x-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" x-model="form.last_name" required />
                                    <div x-show="errors.last_name" class="text-red-500 text-sm mt-1" x-text="errors.last_name"></div>
                                </div>

                                <!-- Email -->
                                <div>
                                    <x-label for="email" :value="__('Email')" />
                                    <x-input id="email" class="block mt-1 w-full" type="email" name="email" x-model="form.email" />
                                    <div x-show="errors.email" class="text-red-500 text-sm mt-1" x-text="errors.email"></div>
                                </div>

                                <!-- Phone -->
                                <div>
                                    <x-label for="phone" :value="__('Phone')" />
                                    <x-input id="phone" class="block mt-1 w-full" type="text" name="phone" x-model="form.phone" />
                                    <div x-show="errors.phone" class="text-red-500 text-sm mt-1" x-text="errors.phone"></div>
                                </div>

                                <!-- Date of Birth -->
                                <div>
                                    <x-label for="date_of_birth" :value="__('Date of Birth')" />
                                    <x-input id="date_of_birth" class="block mt-1 w-full" type="date" name="date_of_birth" x-model="form.date_of_birth" />
                                    <div x-show="errors.date_of_birth" class="text-red-500 text-sm mt-1" x-text="errors.date_of_birth"></div>
                                </div>

                                <!-- Marketing Consent -->
                                <div class="flex items-center mt-6">
                                    <input id="marketing_consent" type="checkbox" name="marketing_consent" x-model="form.marketing_consent" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <label for="marketing_consent" class="ml-2 text-sm text-gray-700">{{ __('Consent to marketing communications') }}</label>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="mt-4">
                                <x-label for="address" :value="__('Address')" />
                                <textarea id="address" name="address" x-model="form.address" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3"></textarea>
                                <div x-show="errors.address" class="text-red-500 text-sm mt-1" x-text="errors.address"></div>
                            </div>

                            <!-- Notes -->
                            <div class="mt-4">
                                <x-label for="notes" :value="__('Notes')" />
                                <textarea id="notes" name="notes" x-model="form.notes" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3"></textarea>
                                <div x-show="errors.notes" class="text-red-500 text-sm mt-1" x-text="errors.notes"></div>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <button type="button" @click="resetForm" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                    {{ __('Reset') }}
                                </button>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150" :disabled="isSubmitting">
                                    <span x-show="isSubmitting" class="inline-block mr-2">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                    {{ __('Create Client') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js Script -->
    <script>
        function clientForm() {
            return {
                form: {
                    first_name: '',
                    last_name: '',
                    email: '',
                    phone: '',
                    date_of_birth: '',
                    address: '',
                    notes: '',
                    marketing_consent: false
                },
                errors: {},
                successMessage: '',
                errorMessage: '',
                isSubmitting: false,

                submitForm() {
                    this.isSubmitting = true;
                    this.errors = {};
                    this.successMessage = '';
                    this.errorMessage = '';

                    // Form validation
                    if (!this.form.first_name) {
                        this.errors.first_name = 'First name is required';
                    }
                    if (!this.form.last_name) {
                        this.errors.last_name = 'Last name is required';
                    }
                    if (this.form.email && !this.validateEmail(this.form.email)) {
                        this.errors.email = 'Please enter a valid email address';
                    }

                    // If there are validation errors, stop submission
                    if (Object.keys(this.errors).length > 0) {
                        this.isSubmitting = false;
                        return;
                    }

                    // Submit the form
                    const form = document.querySelector('form');
                    form.submit();
                },

                resetForm() {
                    this.form = {
                        first_name: '',
                        last_name: '',
                        email: '',
                        phone: '',
                        date_of_birth: '',
                        address: '',
                        notes: '',
                        marketing_consent: false
                    };
                    this.errors = {};
                },

                validateEmail(email) {
                    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    return re.test(String(email).toLowerCase());
                }
            }
        }
    </script>
</x-app-layout>
