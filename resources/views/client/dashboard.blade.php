@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">{{ $title }}</h2>
                    <a href="#" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
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
@endsection
