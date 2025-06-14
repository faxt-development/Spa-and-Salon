@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">{{ $title }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Quick Stats -->
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900">Total Appointments</h3>
                        <p class="mt-2 text-3xl font-bold text-blue-600">0</p>
                    </div>
                    
                    <div class="bg-green-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900">Active Staff</h3>
                        <p class="mt-2 text-3xl font-bold text-green-600">0</p>
                    </div>
                    
                    <div class="bg-yellow-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900">Total Revenue</h3>
                        <p class="mt-2 text-3xl font-bold text-yellow-600">$0.00</p>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-600">No recent activity.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
