@extends('layouts.app')

@section('content')
<div class="py-12 bg-gradient-to-b from-purple-50 to-white">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-purple-600 to-pink-500 overflow-hidden shadow-lg sm:rounded-lg mb-6">
            <div class="p-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-bold mb-2">Welcome to Cleo's Salon and Spa</h2>
                        <p class="text-purple-100">{{ now()->format('l, F j, Y') }}</p>
                    </div>
                    <div class="hidden md:block">
                        <img src="{{ asset('images/cleos-hair-salon.png') }}" alt="Cleo's Salon and Spa" class="h-20 w-auto">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-semibold mb-4 text-purple-800">Dashboard</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Today's Appointments -->
                    <div class="bg-gradient-to-br from-purple-100 to-purple-50 p-6 rounded-lg shadow-md border border-purple-200">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-medium text-purple-800">Today's Appointments</h3>
                            <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-purple-900">{{ $todaysAppointmentsCount ?? 0 }}</p>
                        <a href="{{ route('appointments.index') }}?date={{ date('Y-m-d') }}" class="mt-2 inline-block text-purple-600 hover:text-purple-800 text-sm font-medium">View all appointments →</a>
                    </div>
                    
                    <!-- Total Clients -->
                    <div class="bg-gradient-to-br from-pink-100 to-pink-50 p-6 rounded-lg shadow-md border border-pink-200">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-medium text-pink-800">Total Clients</h3>
                            <svg class="w-8 h-8 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-pink-900">{{ $totalClientsCount ?? 0 }}</p>
                        <a href="{{ route('clients.index') }}" class="mt-2 inline-block text-pink-600 hover:text-pink-800 text-sm font-medium">View all clients →</a>
                    </div>
                    
                    <!-- Total Revenue (This Month) -->
                    <div class="bg-gradient-to-br from-indigo-100 to-indigo-50 p-6 rounded-lg shadow-md border border-indigo-200">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-medium text-indigo-800">Revenue (This Month)</h3>
                            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-indigo-900">${{ $monthlyRevenue ?? '0.00' }}</p>
                        <span class="text-indigo-600 text-sm">From {{ $completedAppointmentsCount ?? 0 }} completed appointments</span>
                    </div>
                </div>
                
                <!-- Upcoming Appointments -->
                <div class="mt-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-medium text-purple-800">Upcoming Appointments</h3>
                        <a href="{{ route('appointments.create') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 active:bg-purple-900 focus:outline-none focus:border-purple-900 focus:ring ring-purple-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            New Appointment
                        </a>
                    </div>
                    <div class="bg-white shadow-md overflow-hidden sm:rounded-lg border border-purple-100">
                        <table class="min-w-full divide-y divide-purple-100">
                            <thead class="bg-purple-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">Client</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">Service</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">Staff</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">Date & Time</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-purple-50">
                                @forelse ($upcomingAppointments ?? [] as $appointment)
                                <tr class="hover:bg-purple-50 transition-colors duration-150 ease-in-out">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-purple-900">{{ $appointment->client->full_name }}</div>
                                        <div class="text-sm text-purple-600">{{ $appointment->client->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm">
                                            @foreach($appointment->services as $service)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mr-1">
                                                    {{ $service->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-700">
                                        {{ $appointment->staff->full_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-700">
                                        {{ $appointment->start_time->format('M d, Y g:i A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @if($appointment->status == 'scheduled') bg-amber-100 text-amber-800
                                            @elseif($appointment->status == 'confirmed') bg-emerald-100 text-emerald-800
                                            @elseif($appointment->status == 'completed') bg-purple-100 text-purple-800
                                            @elseif($appointment->status == 'cancelled') bg-rose-100 text-rose-800
                                            @else bg-slate-100 text-slate-800 @endif">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('appointments.show', $appointment->id) }}" class="text-purple-600 hover:text-purple-900 mr-2">View</a>
                                        <a href="{{ route('appointments.edit', $appointment->id) }}" class="text-pink-600 hover:text-pink-900">Edit</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-purple-500 text-center">
                                        No upcoming appointments
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
