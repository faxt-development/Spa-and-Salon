<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $title }}
            </h2>
            <a href="#" class="inline-flex items-center px-4 py-2 bg-accent-500 text-white rounded-md hover:bg-accent-600 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Book New Appointment
            </a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Configurable Dashboard Area -->
            <x-dashboard.configurable-area />
            
            <div class="border-t border-gray-200 my-6"></div>
            
            <!-- Original Dashboard Content -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">{{ $title }}</h2>
                        <a href="#" class="px-4 py-2 bg-accent-500 text-white rounded-md hover:bg-accent-600 transition">
                            Book New Appointment
                        </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Upcoming Appointments -->
                    <div class="bg-white border rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Upcoming Appointments</h3>
                        <div class="space-y-4">
                            <p class="text-gray-600">You don't have any upcoming appointments.</p>
                        </div>
                    </div>
                    
                    <!-- Special Offers -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-yellow-800 mb-4">Special Offers</h3>
                        <div class="space-y-3">
                            <div class="p-3 bg-yellow-100 rounded-md">
                                <p class="font-medium text-yellow-800">20% Off Your First Visit</p>
                                <p class="text-sm text-yellow-700">Use code: WELCOME20</p>
                            </div>
                            <div class="p-3 bg-yellow-100 rounded-md">
                                <p class="font-medium text-yellow-800">Refer a Friend</p>
                                <p class="text-sm text-yellow-700">Earn $20 credit for each friend who books</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Your Recent Activity</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-600">No recent activity to show.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
