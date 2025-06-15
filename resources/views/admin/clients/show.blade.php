<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Client Profile') }}: {{ $client->first_name }} {{ $client->last_name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.clients.edit', $client->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('admin.clients.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('Back to Clients') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Client Information Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col md:flex-row">
                        <!-- Client Avatar/Initials -->
                        <div class="flex-shrink-0 flex items-center justify-center h-32 w-32 rounded-full bg-gray-200 text-gray-600 text-3xl font-bold mb-4 md:mb-0">
                            {{ substr($client->first_name, 0, 1) }}{{ substr($client->last_name, 0, 1) }}
                        </div>
                        
                        <!-- Client Details -->
                        <div class="md:ml-6 flex-1">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ __('Contact Information') }}</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-600">{{ __('Email') }}:</p>
                                        <p class="font-medium">{{ $client->email ?? 'Not provided' }}</p>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-600">{{ __('Phone') }}:</p>
                                        <p class="font-medium">{{ $client->phone ?? 'Not provided' }}</p>
                                    </div>
                                </div>
                                
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ __('Personal Details') }}</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-600">{{ __('Date of Birth') }}:</p>
                                        <p class="font-medium">{{ $client->date_of_birth ? $client->date_of_birth->format('F j, Y') : 'Not provided' }}</p>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-600">{{ __('Marketing Consent') }}:</p>
                                        <p class="font-medium">{{ $client->marketing_consent ? 'Yes' : 'No' }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Address') }}</h3>
                                <p class="mt-1">{{ $client->address ?? 'No address provided' }}</p>
                            </div>
                            
                            <div class="mt-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Notes') }}</h3>
                                <p class="mt-1">{{ $client->notes ?? 'No notes available' }}</p>
                            </div>
                            
                            <div class="mt-4 flex items-center text-sm text-gray-500">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>{{ __('Client since') }}: {{ $client->created_at->format('F j, Y') }}</span>
                            </div>
                            
                            <div class="mt-1 flex items-center text-sm text-gray-500">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>{{ __('Last visit') }}: {{ $client->last_visit ? $client->last_visit->format('F j, Y') : 'Never' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Client Appointments Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200" x-data="{ activeTab: 'upcoming' }">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Appointments') }}</h3>
                        <a href="#" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            {{ __('Book New Appointment') }}
                        </a>
                    </div>
                    
                    <!-- Tabs -->
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8">
                            <button @click="activeTab = 'upcoming'" :class="{'border-indigo-500 text-indigo-600': activeTab === 'upcoming', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'upcoming'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                {{ __('Upcoming') }}
                            </button>
                            <button @click="activeTab = 'past'" :class="{'border-indigo-500 text-indigo-600': activeTab === 'past', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'past'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                {{ __('Past') }}
                            </button>
                            <button @click="activeTab = 'canceled'" :class="{'border-indigo-500 text-indigo-600': activeTab === 'canceled', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'canceled'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                {{ __('Canceled') }}
                            </button>
                        </nav>
                    </div>
                    
                    <!-- Appointment Lists -->
                    <div class="mt-4">
                        <!-- Upcoming Appointments -->
                        <div x-show="activeTab === 'upcoming'">
                            <p class="text-gray-500">{{ __('No upcoming appointments.') }}</p>
                            <!-- This would be replaced with actual appointment data from the controller -->
                        </div>
                        
                        <!-- Past Appointments -->
                        <div x-show="activeTab === 'past'" x-cloak>
                            <p class="text-gray-500">{{ __('No past appointments.') }}</p>
                            <!-- This would be replaced with actual appointment data from the controller -->
                        </div>
                        
                        <!-- Canceled Appointments -->
                        <div x-show="activeTab === 'canceled'" x-cloak>
                            <p class="text-gray-500">{{ __('No canceled appointments.') }}</p>
                            <!-- This would be replaced with actual appointment data from the controller -->
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Client Purchases Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Purchase History') }}</h3>
                    </div>
                    
                    <p class="text-gray-500">{{ __('No purchase history available.') }}</p>
                    <!-- This would be replaced with actual purchase data from the controller -->
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
