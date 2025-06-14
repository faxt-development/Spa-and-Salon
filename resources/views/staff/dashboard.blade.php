@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">{{ $title }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Today's Schedule -->
                    <div class="bg-white border rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Today's Appointments</h3>
                        <div class="space-y-4">
                            <p class="text-gray-600">No appointments scheduled for today.</p>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="bg-white border rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="#" class="block px-4 py-2 bg-blue-100 text-blue-800 rounded-md hover:bg-blue-200 transition">
                                Check Schedule
                            </a>
                            <a href="#" class="block px-4 py-2 bg-green-100 text-green-800 rounded-md hover:bg-green-200 transition">
                                Add New Client
                            </a>
                            <a href="#" class="block px-4 py-2 bg-purple-100 text-purple-800 rounded-md hover:bg-purple-200 transition">
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
@endsection
