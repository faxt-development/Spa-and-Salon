<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Email Marketing Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Total Sent -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium mb-1">Total Emails Sent</div>
                    <div class="text-3xl font-bold text-gray-800">{{ number_format($stats['total_sent']) }}</div>
                    <div class="mt-2 text-sm text-gray-500">
                        <span class="font-medium">{{ number_format($stats['campaigns']['sent']) }}</span> from campaigns,
                        <span class="font-medium">{{ number_format($stats['drip']['sent']) }}</span> from drip campaigns
                    </div>
                </div>

                <!-- Open Rate -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium mb-1">Open Rate</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['open_rate'] }}%</div>
                    <div class="mt-2 text-sm text-gray-500">
                        <span class="font-medium">{{ number_format($stats['total_opened']) }}</span> emails opened
                    </div>
                </div>

                <!-- Click Rate -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium mb-1">Click Rate</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['click_rate'] }}%</div>
                    <div class="mt-2 text-sm text-gray-500">
                        <span class="font-medium">{{ number_format($stats['total_clicked']) }}</span> emails clicked
                    </div>
                </div>

                <!-- Unsubscribe Rate -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium mb-1">Unsubscribe Rate</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['unsubscribe_rate'] }}%</div>
                    <div class="mt-2 text-sm text-gray-500">
                        <span class="font-medium">{{ number_format($stats['total_unsubscribed']) }}</span> unsubscribes
                    </div>
                </div>
            </div>

            <!-- Additional Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Total Campaigns -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium mb-1">Total Campaigns</div>
                    <div class="text-3xl font-bold text-gray-800">{{ number_format($stats['campaigns']['total']) }}</div>
                    <div class="mt-2 text-sm text-gray-500">
                        <span class="font-medium">{{ number_format($stats['campaigns']['active']) }}</span> active campaigns
                    </div>
                </div>

                <!-- Total Drip Campaigns -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium mb-1">Total Drip Campaigns</div>
                    <div class="text-3xl font-bold text-gray-800">{{ number_format($stats['drip']['total']) }}</div>
                    <div class="mt-2 text-sm text-gray-500">
                        <span class="font-medium">{{ number_format($stats['drip']['active']) }}</span> active drip campaigns
                    </div>
                </div>

                <!-- Click-to-Open Rate -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium mb-1">Click-to-Open Rate</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['click_to_open_rate'] }}%</div>
                    <div class="mt-2 text-sm text-gray-500">
                        Percentage of opened emails that were clicked
                    </div>
                </div>

                <!-- Subscribers -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium mb-1">Subscribers</div>
                    <div class="text-3xl font-bold text-gray-800">{{ number_format($stats['subscribers']) }}</div>
                    <div class="mt-2 text-sm text-gray-500">
                        <span class="font-medium">{{ number_format($stats['unsubscribers']) }}</span> unsubscribed
                    </div>
                </div>
            </div>

            <!-- Performance Chart -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Email Performance (Last 30 Days)</h3>
                <div class="h-80" id="performance-chart"></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Campaigns -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Recent Campaigns</h3>
                        <a href="{{ route('email-campaigns.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View All</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Open Rate</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($recentCampaigns as $campaign)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('email-campaigns.show', $campaign) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{ $campaign->name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $campaign->recipients()->whereNotNull('sent_at')->count() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @php
                                                $sent = $campaign->recipients()->whereNotNull('sent_at')->count();
                                                $opened = $campaign->recipients()->whereNotNull('opened_at')->count();
                                                $openRate = $sent > 0 ? round(($opened / $sent) * 100, 2) : 0;
                                            @endphp
                                            {{ $openRate }}%
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($campaign->is_active)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No campaigns found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Drip Campaigns -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Recent Drip Campaigns</h3>
                        <a href="{{ route('drip-campaigns.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View All</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($recentDripCampaigns as $campaign)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('drip-campaigns.show', $campaign) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{ $campaign->name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $campaign->type }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $campaign->recipients()->whereNotNull('sent_at')->count() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($campaign->is_active)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No drip campaigns found
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

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dailyStats = @json($dailyStats);
            
            const dates = dailyStats.map(item => item.date);
            const sentCounts = dailyStats.map(item => item.sent);
            const openedCounts = dailyStats.map(item => item.opened);
            
            const options = {
                chart: {
                    type: 'line',
                    height: 350,
                    toolbar: {
                        show: false
                    }
                },
                series: [
                    {
                        name: 'Sent',
                        data: sentCounts
                    },
                    {
                        name: 'Opened',
                        data: openedCounts
                    }
                ],
                xaxis: {
                    categories: dates,
                    labels: {
                        formatter: function(value) {
                            return new Date(value).toLocaleDateString('en-US', {
                                month: 'short',
                                day: 'numeric'
                            });
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'Count'
                    },
                    min: 0
                },
                colors: ['#4f46e5', '#10b981'],
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                dataLabels: {
                    enabled: false
                },
                tooltip: {
                    shared: true,
                    intersect: false
                },
                grid: {
                    borderColor: '#e2e8f0',
                    row: {
                        colors: ['#f8fafc', 'transparent']
                    }
                },
                markers: {
                    size: 4
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right'
                }
            };
            
            const chart = new ApexCharts(document.querySelector("#performance-chart"), options);
            chart.render();
        });
    </script>
    @endpush
</x-app-layout>
