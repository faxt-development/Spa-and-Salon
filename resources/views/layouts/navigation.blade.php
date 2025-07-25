@php
    $company = $company ?? null;
    $companyName = $company->name ?? config('app.name');
    $theme = $company->theme_settings ?? [
        'primary' => '#4f46e5',
        'secondary' => '#10b981',
        'accent' => '#f59e0b',
        'logo' => null
    ];
@endphp

<nav x-data="{ mobileMenuOpen: false }" class="bg-white border-b border-gray-100 shadow-sm" @keydown.escape="mobileMenuOpen = false">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    @auth
                        @role('admin|staff')
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center">
                            @if(isset($company->logo_url))
                                <img src="{{ $company->logo_url }}" alt="{{ $companyName }} Logo" class="h-8 w-auto">
                            @else
                                <x-application-logo class="block h-9 w-auto fill-current text-primary-600" />
                            @endif
                        </a>
                        @else
                        <a href="{{ route('dashboard') }}" class="flex items-center">
                            @if(isset($company->logo_url))
                                <img src="{{ $company->logo_url }}" alt="{{ $companyName }} Logo" class="h-8 w-auto">
                            @else
                                <x-application-logo class="block h-9 w-auto fill-current text-primary-600" />
                            @endif
                        </a>
                        @endrole
                    @else
                        <a href="{{ route('dashboard') }}" class="flex items-center">
                            @if(isset($company->logo_url))
                                <img src="{{ $company->logo_url }}" alt="{{ $companyName }} Logo" class="h-8 w-auto">
                            @else
                                <x-application-logo class="block h-9 w-auto fill-current text-primary-600" />
                            @endif
                        </a>
                    @endauth
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @auth
                        @role('admin|staff')
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard') || request()->is('admin*')" class="text-gray-700 hover:text-primary-600 hover:border-primary-500 focus:outline-none focus:border-primary-500 transition duration-150 ease-in-out">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        @else
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-gray-700 hover:text-primary-600 hover:border-primary-500 focus:outline-none focus:border-primary-500 transition duration-150 ease-in-out">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        @endrole

                        @role('admin|staff')
                        <!-- Appointments Dropdown -->
                        <div class="relative inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150" x-data="{ open: false }" @keydown.escape="open = false" @click.away="open = false">
                            <x-nav-link href="{{ route('web.appointments.index') }}" @click.prevent="open = !open"
                                    @keydown.enter.prevent="open = !open"
                                    @keydown.space.prevent="open = !open"
                                    :active="request()->routeIs('web.appointments.*') || request()->routeIs('admin.appointments.*')">
                                <div class="inline-flex items-center">
                                    {{ __('Appointments') }}
                                    <svg class="ml-1 h-4 w-4" :class="{ 'transform rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </x-nav-link>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="origin-top-right absolute left-0 mt-36 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                                 role="menu"
                                 aria-orientation="vertical"
                                 aria-labelledby="appointments-menu"
                                 tabindex="-1"
                                 x-cloak>
                                <div class="py-1" role="none">
                                    <x-dropdown-link :href="route('web.appointments.index')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('View Appointments') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('web.appointments.create')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('New Appointment') }}
                                    </x-dropdown-link>
                                    @role('admin')
                                    <x-dropdown-link :href="route('admin.appointments.settings')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Appointment Settings') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.appointments.reminders')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Appointment Reminders') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.appointments.policies')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Cancellation Policies') }}
                                    </x-dropdown-link>
                                    @endrole
                                </div>
                            </div>
                        </div>
                        @endrole

                        @role('admin')
                        <!-- Staff Management Dropdown -->
                        <div class="relative inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150" x-data="{ open: false }" @keydown.escape="open = false" @click.away="open = false">
                            <x-nav-link href="{{ route('admin.staff.index') }}" @click.prevent="open = !open"
                                    @keydown.enter.prevent="open = !open"
                                    @keydown.space.prevent="open = !open"
                                    :active="request()->routeIs('admin.staff.*')">
                                <div class="inline-flex items-center">
                                    {{ __('Staff') }}
                                    <svg class="ml-1 h-4 w-4" :class="{ 'transform rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </x-nav-link>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="origin-top-right absolute left-0 mt-36 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                                 role="menu"
                                 aria-orientation="vertical"
                                 aria-labelledby="staff-menu"
                                 tabindex="-1"
                                 x-cloak>
                                <div class="py-1" role="none">
                                    <x-dropdown-link :href="route('admin.staff.index')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Staff Directory') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.staff.create')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Add New Staff') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.staff.roles')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Roles & Permissions') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.staff.availability')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Staff Availability') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.staff.services')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Assign Services to Staff') }}
                                    </x-dropdown-link>
                                </div>
                            </div>
                        </div>

                        <!-- Business Management Dropdown -->
                        <div class="relative inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150" x-data="{ open: false }" @keydown.escape="open = false" @click.away="open = false">
                            <x-nav-link href="#" @click.prevent="open = !open"
                                    @keydown.enter.prevent="open = !open"
                                    @keydown.space.prevent="open = !open"
                                    :active="request()->routeIs('admin.company.*') || request()->routeIs('admin.services.categories.*')">
                                <div class="inline-flex items-center">
                                    {{ __('Business') }}
                                    <svg class="ml-1 h-4 w-4" :class="{ 'transform rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </x-nav-link>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="origin-top-right absolute left-0 mt-36 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                                 role="menu"
                                 aria-orientation="vertical"
                                 aria-labelledby="business-menu"
                                 tabindex="-1"
                                 x-cloak>
                                <div class="py-1" role="none">
                                    <x-dropdown-link :href="route('admin.company.edit')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Company Settings') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.services')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Services') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.services.categories')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Service Categories') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.services.packages')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Service Packages') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.locations.index')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Locations') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.appointments.settings')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Appointment Settings') }}
                                    </x-dropdown-link>
                                </div>
                            </div>
                        </div>

                        <!-- Reports Dropdown -->
                        <div class="relative inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150" x-data="{ open: false }" @keydown.escape="open = false" @click.away="open = false">
                            <x-nav-link href="{{ route('admin.payroll.reports.tax') }}" @click.prevent="open = !open"
                                    @keydown.enter.prevent="open = !open"
                                    @keydown.space.prevent="open = !open"
                                    :active="request()->routeIs('admin.reports.*')">
                                <div class="inline-flex items-center">
                                    {{ __('Reports') }}
                                    <svg class="ml-1 h-4 w-4" :class="{ 'transform rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </x-nav-link>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="origin-top-right absolute left-0 mt-36 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                                 role="menu"
                                 aria-orientation="vertical"
                                 aria-labelledby="reports-menu"
                                 tabindex="-1"
                                 x-cloak>
                                <div class="py-1" role="none">
                                    <x-dropdown-link href="{{ route('admin.reports.sales') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Sales Reports') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link href="{{ route('reports.clients.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Client Spend Analytics') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link href="{{ route('admin.reports.tax') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Tax Reports') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link href="{{ route('admin.reports.service.categories') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Service Categories') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link href="{{ route('admin.reports.payment-methods') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Payment Methods') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link href="{{ route('admin.payroll.reports.tax') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Payroll Tax Reports') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link href="{{ route('admin.payroll.reports.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Payroll Reports') }}
                                    </x-dropdown-link>
                                </div>
                            </div>
                        </div>

                        <!-- Business Management Dropdown -->

                        <!-- Email Marketing Dropdown -->
                        <div class="relative inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150" x-data="{ open: false }" @keydown.escape="open = false" @click.away="open = false">
                            <x-nav-link href="{{ route('email-marketing.dashboard') }}" @click.prevent="open = !open"
                                    @keydown.enter.prevent="open = !open"
                                    @keydown.space.prevent="open = !open"
                                    :active="request()->routeIs('email-marketing.*')">
                                <div class="inline-flex items-center">
                                    {{ __('Email Marketing') }}
                                    <svg class="ml-1 h-4 w-4" :class="{ 'transform rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </x-nav-link>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="origin-top-right absolute right-0 mt-36 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                                 role="menu"
                                 aria-orientation="vertical"
                                 aria-labelledby="email-marketing-menu"
                                 tabindex="-1"
                                 x-cloak>
                                <div class="py-1" role="none">
                                    <x-dropdown-link href="{{ route('admin.email-campaigns.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Campaigns') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link href="{{ route('drip-campaigns.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Drip Campaigns') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link href="{{ route('email-marketing.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Dashboard') }}
                                    </x-dropdown-link>
                                </div>
                            </div>
                        </div>

                        @endrole

                        <!-- Help Section -->
                        <div class="relative inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150" x-data="{ open: false }" @keydown.escape="open = false" @click.away="open = false">
                            <x-nav-link href="#" @click.prevent="open = !open"
                                    @keydown.enter.prevent="open = !open"
                                    @keydown.space.prevent="open = !open"
                                    :active="request()->routeIs('help.*')">
                                <div class="inline-flex items-center">
                                    {{ __('Help') }}
                                    <svg class="ml-1 h-4 w-4" :class="{ 'transform rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </x-nav-link>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="origin-top-right absolute left-0 mt-36 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                                 role="menu"
                                 aria-orientation="vertical"
                                 aria-labelledby="help-menu"
                                 tabindex="-1"
                                 x-cloak>
                                <div class="py-1" role="none">
                                    <x-dropdown-link :href="route('admin.help.appointments')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('How to Schedule Appointments') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.support.docs')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Documentation') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.support.contacts')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        {{ __('Contact Support') }}
                                    </x-dropdown-link>
                                </div>
                            </div>
                        </div>

                    @endauth
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                <div class="relative ml-3" x-data="{ open: false }" @keydown.escape="open = false" @click.away="open = false">
                    <button @click="open = !open"
                            @keydown.enter.prevent="open = !open"
                            @keydown.space.prevent="open = !open"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150">
                        <div>{{ Auth::user()->name }}</div>
                        <svg class="ml-1 h-4 w-4" :class="{ 'transform rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                         role="menu"
                         aria-orientation="vertical"
                         aria-labelledby="user-menu"
                         tabindex="-1"
                         x-cloak>
                        <div class="py-1" role="none">
                            <x-dropdown-link :href="route('profile.edit')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:outline-none" role="menuitem" tabindex="-1">
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @else
                <div class="ml-3">
                    <a href="{{ route('login') }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150">
                        Login
                    </a>
                    @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150">
                        Register
                    </a>
                    @endif
                </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
                @role('admin|staff')
                    <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard') || request()->is('admin*')">
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>
                @else
                    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>
                @endrole
            @else
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            @auth
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
            @else
            <div class="px-4 py-2">
                <x-responsive-nav-link :href="route('login')">
                    {{ __('Login') }}
                </x-responsive-nav-link>
                @if (Route::has('register'))
                <x-responsive-nav-link :href="route('register')">
                    {{ __('Register') }}
                </x-responsive-nav-link>
                @endif
            </div>
            @endauth
        </div>
    </div>
</nav>
