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
                            @if($client->total_spent > 0)
                                <div class="absolute -bottom-1 -right-1 bg-green-500 text-white text-xs font-bold rounded-full h-6 w-6 flex items-center justify-center">
                                    {{ $client->total_appointments }}
                                </div>
                            @endif
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

                            <!-- Client Stats Grid -->
                            <div class="mt-4 grid grid-cols-2 gap-4">
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <p class="text-xs text-gray-500">Lifetime Value</p>
                                    <p class="text-lg font-semibold">${{ number_format($client->lifetime_value, 2) }}</p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <p class="text-xs text-gray-500">Total Visits</p>
                                    <p class="text-lg font-semibold">{{ $client->total_appointments }}</p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <p class="text-xs text-gray-500">Avg. Spend/Visit</p>
                                    <p class="text-lg font-semibold">${{ number_format($client->average_visit_value, 2) }}</p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <p class="text-xs text-gray-500">Days Since Last Visit</p>
                                    <p class="text-lg font-semibold">{{ $client->days_since_last_visit ?? 'N/A' }}</p>
                                </div>
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

                            @if($client->source)
                            <div class="mt-1 flex items-center text-sm text-gray-500">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                </svg>
                                <span>{{ __('Source') }}: {{ ucfirst(str_replace('_', ' ', $client->source)) }}</span>
                            </div>
                            @endif
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
                        </div>

                        <!-- Past Appointments -->
                        <div x-show="activeTab === 'past'" x-cloak>
                            @if($client->appointments->count() > 0)
                                <div class="space-y-4">
                                    @foreach($client->appointments as $appointment)
                                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                                            <div class="flex justify-between">
                                                <div>
                                                    <p class="font-medium">{{ $appointment->start_time->format('F j, Y \a\t g:i A') }}</p>
                                                    <p class="text-sm text-gray-600">
                                                        {{ $appointment->services->pluck('name')->implode(', ') }}
                                                    </p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="font-medium">${{ number_format($appointment->total_price, 2) }}</p>
                                                    <p class="text-sm text-gray-600">{{ $appointment->status }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500">{{ __('No past appointments.') }}</p>
                            @endif
                        </div>

                        <!-- Canceled Appointments -->
                        <div x-show="activeTab === 'canceled'" x-cloak>
                            <p class="text-gray-500">{{ __('No canceled appointments.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client Spend Analytics -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Spend Analytics') }}</h3>
                        <div class="flex space-x-2">
                            <a href="#" onclick="window.print()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Print
                            </a>
                            <a href="{{ route('reports.clients.export.single', $client) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Export
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                        <!-- Summary Cards -->
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Lifetime Value</p>
                                    <p class="text-2xl font-semibold text-gray-900">${{ number_format($client->lifetime_value, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100 text-green-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Visits</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $client->appointments()->where('status', 'completed')->count() }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-primary50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-primary100 text-primary600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Avg. Spend/Visit</p>
                                    <p class="text-2xl font-semibold text-gray-900">${{ number_format($client->average_visit_value, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Spend Trend Chart -->
                        <div class="bg-white p-4 border border-gray-200 rounded-lg">
                            <h4 class="font-medium text-gray-700 mb-3">{{ __('Spend Trend (Last 12 Months)') }}</h4>
                            <div class="h-64">
                                <canvas id="spendTrendChart"></canvas>
                            </div>
                        </div>

                        <!-- Spend by Category -->
                        <div class="bg-white p-4 border border-gray-200 rounded-lg">
                            <h4 class="font-medium text-gray-700 mb-3">{{ __('Spend by Category') }}</h4>
                            @if($spendByCategory->isNotEmpty())
                                <div class="h-64">
                                    <canvas id="spendByCategoryChart"></canvas>
                                </div>
                                <div class="mt-3 space-y-2 max-h-40 overflow-y-auto pr-2">
                                    @foreach($spendByCategory as $category)
                                        <div class="flex items-center justify-between text-sm">
                                            <div class="flex items-center">
                                                <span class="inline-block w-3 h-3 rounded-full mr-2" style="background-color: {{ '#' . substr(md5($category->category), 0, 6) }}"></span>
                                                <span>{{ $category->category }}</span>
                                            </div>
                                            <span class="font-medium">${{ number_format($category->total_spent, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500">{{ __('No spend data available.') }}</p>
                            @endif
                        </div>

                        <!-- Payment Methods -->
                        <div class="bg-white p-4 border border-gray-200 rounded-lg">
                            <h4 class="font-medium text-gray-700 mb-3">{{ __('Payment Methods') }}</h4>
                            @if($paymentMethods->isNotEmpty())
                                <div class="h-64">
                                    <canvas id="paymentMethodsChart"></canvas>
                                </div>
                                <div class="mt-3 space-y-2">
                                    @foreach($paymentMethods as $method)
                                        <div class="flex justify-between text-sm">
                                            <div class="flex items-center">
                                                <span class="inline-block w-3 h-3 rounded-full mr-2" style="background-color: {{ '#' . substr(md5($method->payment_method), 0, 6) }}"></span>
                                                <span>{{ ucfirst($method->payment_method) }}</span>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-medium">${{ number_format($method->total_amount, 2) }}</div>
                                                <div class="text-xs text-gray-500">{{ $method->transaction_count }} {{ Str::plural('transaction', $method->transaction_count) }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500">{{ __('No payment data available.') }}</p>
                            @endif
                        </div>

                        <!-- Recent Transactions -->
                        <div class="bg-white p-4 border border-gray-200 rounded-lg">
                            <h4 class="font-medium text-gray-700 mb-3">{{ __('Recent Transactions') }}</h4>
                            @php
                                $recentPayments = $client->payments()
                                                    ->with('appointment')
                                                    ->where('status', 'completed')
                                                    ->orderBy('created_at', 'desc')
                                                    ->take(5)
                                                    ->get();
                            @endphp

                            @if($recentPayments->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach($recentPayments as $payment)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <div class="font-medium">${{ number_format($payment->amount, 2) }}</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $payment->appointment ? $payment->appointment->service->name ?? 'Service' : 'Payment' }}
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $payment->created_at->format('M d, Y') }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ ucfirst($payment->payment_method) }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @if($client->payments()->count() > 5)
                                    <div class="mt-3 text-center">
                                        <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                            View all {{ $client->payments()->count() }} transactions
                                        </a>
                                    </div>
                                @endif
                            @else
                                <p class="text-gray-500">{{ __('No recent transactions.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Spend Trend Chart
            const spendTrendCtx = document.getElementById('spendTrendChart');
            if (spendTrendCtx) {
                const spendTrendData = @json($client->getSpendTrend(12));

                new Chart(spendTrendCtx, {
                    type: 'line',
                    data: {
                        labels: spendTrendData.map(item => item.month),
                        datasets: [{
                            label: 'Monthly Spend ($)',
                            data: spendTrendData.map(item => item.total),
                            backgroundColor: 'rgba(79, 70, 229, 0.05)',
                            borderColor: 'rgba(79, 70, 229, 0.8)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true,
                            pointBackgroundColor: 'white',
                            pointBorderColor: 'rgba(79, 70, 229, 1)',
                            pointBorderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    drawBorder: false
                                },
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value;
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Spend: $' + context.parsed.y.toFixed(2);
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Spend by Category Chart
            const spendByCategoryCtx = document.getElementById('spendByCategoryChart');
            if (spendByCategoryCtx) {
                const spendByCategoryData = @json($spendByCategory);
                const colors = spendByCategoryData.map(item => '#' + md5(item.category).substring(0, 6));

                new Chart(spendByCategoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: spendByCategoryData.map(item => item.category),
                        datasets: [{
                            data: spendByCategoryData.map(item => item.total_spent),
                            backgroundColor: colors,
                            borderWidth: 1,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: $${value.toFixed(2)} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Payment Methods Chart
            const paymentMethodsCtx = document.getElementById('paymentMethodsChart');
            if (paymentMethodsCtx) {
                const paymentMethodsData = @json($paymentMethods);
                const colors = paymentMethodsData.map(item => '#' + md5(item.payment_method).substring(0, 6));

                new Chart(paymentMethodsCtx, {
                    type: 'pie',
                    data: {
                        labels: paymentMethodsData.map(item => item.payment_method.charAt(0).toUpperCase() + item.payment_method.slice(1)),
                        datasets: [{
                            data: paymentMethodsData.map(item => item.total_amount),
                            backgroundColor: colors,
                            borderWidth: 1,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    boxWidth: 10,
                                    padding: 15
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: $${value.toFixed(2)} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Simple MD5 function for generating colors
            function md5(string) {
                return Array.from(Array(32), (_, i) => (i < 8 || i > 10 ? '0' : '') + (i === 3 || i === 5 || i === 7 || i === 9 ? '4' : '') + (i === 11 ? (Math.random() * 4 | 8).toString(16) : (Math.random() * 16 | 0).toString(16))).join('');
            }
        });
    </script>
    @endpush
</x-app-layout>
