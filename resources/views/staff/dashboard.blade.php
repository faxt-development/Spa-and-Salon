<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $title }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">{{ $title }}</h2>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Today's Appointments</h3>
                        <div class="space-y-4">
                            <p class="text-gray-600">No appointments scheduled for today.</p>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white border rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="#" class="block px-4 py-2 bg-accent-50 text-accent-800 rounded-md hover:bg-accent-100 transition">
                                Check Schedule
                            </a>
                            <a href="#" class="block px-4 py-2 bg-accent-50 text-accent-800 rounded-md hover:bg-accent-100 transition">
                                Add New Client
                            </a>
                            <a href="#" class="block px-4 py-2 bg-primary-100 text-primary-800 rounded-md hover:bg-primary-200 transition">
                                View Client List
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Messages -->
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Messages</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-600">No new messages.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
