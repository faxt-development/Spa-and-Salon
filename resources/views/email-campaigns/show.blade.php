@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.min.css">
@endpush

@section('content')
<div class="py-6" x-data="campaignData()" x-init="init()">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Header with back button and actions -->
                <div class="md:flex md:items-center md:justify-between mb-6">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center">
                            <a href="{{ route('admin.email-campaigns.index') }}" class="mr-4 text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                            </a>
                            <h2 class="text-2xl font-semibold text-gray-800">{{ $campaign->name }}</h2>
                            <span class="ml-4 px-2.5 py-0.5 rounded-full text-xs font-medium {{
                                $campaign->status === 'sent' ? 'bg-green-100 text-green-800' :
                                ($campaign->status === 'scheduled' ? 'bg-blue-100 text-blue-800' :
                                ($campaign->status === 'sending' ? 'bg-yellow-100 text-yellow-800' :
                                ($campaign->status === 'draft' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800')))
                            }}">
                                {{ ucfirst($campaign->status) }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">
                            Created {{ $campaign->created_at->diffForHumans() }}
                            @if($campaign->sent_at)
                                • Sent {{ $campaign->sent_at->diffForHumans() }}
                            @elseif($campaign->scheduled_for)
                                • Scheduled for {{ $campaign->scheduled_for->format('M j, Y g:i A') }}
                            @endif
                        </p>
                    </div>
                    <div class="mt-4 flex md:mt-0 md:ml-4">
                        @if($campaign->isDraft())
                            <a href="{{ route('email-campaigns.edit', $campaign) }}"
                               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Edit
                            </a>
                            <form action="{{ route('email-campaigns.send', $campaign) }}" method="POST" class="ml-3">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Send Now
                                </button>
                            </form>
                        @elseif($campaign->isScheduled())
                            <form action="{{ route('email-campaigns.cancel', $campaign) }}" method="POST" class="ml-3">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    Cancel Scheduled Send
                                </button>
                            </form>
                        @endif

                        @if($campaign->isDraft() || $campaign->isScheduled())
                            <form action="{{ route('email-campaigns.destroy', $campaign) }}" method="POST" class="ml-3">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Are you sure you want to delete this campaign? This action cannot be undone.')"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Campaign Stats -->
                @if($campaign->isSent() || $campaign->isSending())
                <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Campaign Performance</h3>
                        <p class="mt-1 text-sm text-gray-500">Overview of how your email campaign is performing</p>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 p-6">
                        <!-- Sent -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-50 text-blue-600">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Sent</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['sent']) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Open Rate -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-50 text-green-600">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Open Rate</p>
                                    <div class="flex items-baseline">
                                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['open_rate'] }}%</p>
                                        <p class="ml-2 text-sm text-gray-500">{{ number_format($stats['opened']) }} opened</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Click Rate -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-primary-50 text-primary-600">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Click Rate</p>
                                    <div class="flex items-baseline">
                                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['click_rate'] }}%</p>
                                        <p class="ml-2 text-sm text-gray-500">{{ number_format($stats['clicked']) }} clicked</p>
                                    </div>
                                    @if($stats['opened'] > 0)
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $stats['click_to_open_rate'] }}% of opens
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Bounce/Unsubscribe -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-amber-50 text-amber-600">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Bounced</p>
                                    <div class="flex items-baseline">
                                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['bounce_rate'] }}%</p>
                                        <p class="ml-2 text-sm text-gray-500">{{ number_format($stats['bounced']) }} emails</p>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Bounce Rate</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 mr-4">
                                            <div class="bg-red-600 h-2.5 rounded-full" style="width: {{ $campaign->bounce_rate }}%"></div>
                                        </div>
                                        <span>{{ number_format($campaign->bounce_rate, 1) }}% ({{ number_format($campaign->bounced_count) }} bounced)</span>
                                    </div>
                                </dd>
                            </div>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Unsubscribe Rate</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 mr-4">
                                            <div class="bg-yellow-600 h-2.5 rounded-full" style="width: {{ $campaign->unsubscribe_rate }}%"></div>
                                        </div>
                                        <span>{{ number_format($campaign->unsubscribe_rate, 1) }}% ({{ number_format($campaign->unsubscribed_count) }} unsubscribed)</span>
                                    </div>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
                @endif

                @if($campaign->isSent() && count($timeline) > 0)
                <!-- Engagement Timeline -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Engagement Over Time</h3>
                        <p class="mt-1 text-sm text-gray-500">Track how recipients are interacting with your campaign over time</p>
                    </div>
                    <div class="p-6">
                        <div id="engagement-timeline" class="h-80"></div>
                    </div>
                </div>
                @endif

                @if($campaign->isSent() && (count($devices) > 0 || count($platforms) > 0))
                <!-- Device & Platform Distribution -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Device & Platform Distribution</h3>
                        <p class="mt-1 text-sm text-gray-500">See which devices and platforms your recipients are using</p>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                        @if(count($devices) > 0)
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-4">Devices</h4>
                            <div id="device-distribution" class="h-64"></div>
                        </div>
                        @endif

                        @if(count($platforms) > 0)
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-4">Platforms</h4>
                            <div id="platform-distribution" class="h-64"></div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @if($campaign->isSent() && count($topLinks) > 0)
                <!-- Top Clicked Links -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Top Clicked Links</h3>
                        <p class="mt-1 text-sm text-gray-500">See which links were clicked the most in your email</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Link</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clicks</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unique Clicks</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CTR</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($topLinks as $link)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full bg-primary-100">
                                                <svg class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 truncate max-w-xs" x-data="{ showFull: false }" @click="showFull = !showFull">
                                                    <span x-show="!showFull" class="cursor-pointer hover:text-primary-600">
                                                        {{ Str::limit(parse_url($link['url'], PHP_URL_PATH) ?: $link['url'], 50) }}
                                                    </span>
                                                    <span x-show="showFull" class="cursor-pointer text-primary-600 break-all">
                                                        {{ $link['url'] }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ parse_url($link['url'], PHP_URL_HOST) }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $link['clicks'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $link['unique_clicks'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $ctr = $stats['sent'] > 0 ? ($link['clicks'] / $stats['sent']) * 100 : 0;
                                        @endphp
                                        <div class="flex items-center">
                                            <div class="w-24 bg-gray-200 rounded-full h-2.5 mr-2">
                                                <div class="bg-primary-600 h-2.5 rounded-full" style="width: {{ min($ctr, 100) }}%"></div>
                                            </div>
                                            <span class="text-sm font-medium text-gray-700">{{ number_format($ctr, 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Email Preview -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Email Preview</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">How your email will appear to recipients</p>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <div class="border border-gray-200 rounded-md p-4 max-w-3xl mx-auto">
                            <div class="border-b border-gray-200 pb-2 mb-4">
                                <div class="font-semibold">{{ $campaign->subject }}</div>
                                <div class="text-xs text-gray-500">
                                    To: You
                                    @if($campaign->preview_text)
                                        <div class="mt-1 text-gray-400">{{ $campaign->preview_text }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="prose max-w-none">
                                {!! $campaign->content !!}
                            </div>
                            @if($campaign->isSent())
                            <div class="mt-6 pt-4 border-t border-gray-200 text-sm text-gray-500 text-center">
                                <p>This is a preview. Actual email was sent to {{ number_format($campaign->sent_count) }} recipients.</p>
                                <p class="mt-1">
                                    <a href="#" class="text-blue-600 hover:text-blue-800">View all recipients</a>
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recipient Activity (if sent) -->
                @if($campaign->isSent())
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Recipient Activity</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">Detailed tracking of how recipients are engaging with your email</p>
                    </div>
                    <div class="px-4 py-5 sm:p-0">
                        <div class="border-t border-gray-200">
                            <div class="sm:hidden">
                                <!-- Mobile view -->
                                @forelse($campaign->recipients()->latest()->take(10)->get() as $recipient)
                                    <div class="px-4 py-3 border-b border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <div class="text-sm font-medium text-gray-900 truncate">{{ $recipient->name ?? $recipient->email }}</div>
                                            <div class="flex items-center space-x-2">
                                                @if($recipient->opened_at)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800" title="Opened {{ $recipient->opened_at->diffForHumans() }}">
                                                        Opened
                                                    </span>
                                                @endif
                                                @if($recipient->clicked_at)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800" title="Clicked {{ $recipient->clicked_at->diffForHumans() }}">
                                                        Clicked
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mt-1 text-sm text-gray-500">{{ $recipient->email }}</div>
                                    </div>
                                @empty
                                    <div class="px-4 py-5 text-center text-gray-500">
                                        No recipient activity to display yet.
                                    </div>
                                @endforelse
                            </div>
                            <div class="hidden sm:block">
                                <!-- Desktop view -->
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opened</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clicked</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Activity</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($campaign->recipients()->latest()->take(10)->get() as $recipient)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full bg-gray-100">
                                                            <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M12 12a5 5 0 110-10 5 5 0 010 10zm0-2a3 3 0 100-6 3 3 0 000 6zm7 11v-2.5a5.5 5.5 0 00-5.5-5.5h-3A5.5 5.5 0 005 18.5V21h14z" />
                                                            </svg>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">{{ $recipient->name ?? 'No Name' }}</div>
                                                            <div class="text-sm text-gray-500">{{ $recipient->email }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($recipient->bounced_at)
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                            Bounced
                                                        </span>
                                                    @elseif($recipient->complained_at)
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                            Complained
                                                        </span>
                                                    @elseif($recipient->unsubscribed_at)
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                            Unsubscribed
                                                        </span>
                                                    @elseif($recipient->delivered_at)
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            Delivered
                                                        </span>
                                                    @else
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                            Pending
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($recipient->opened_at)
                                                        <div class="text-sm text-gray-900">{{ $recipient->opened_at->diffForHumans() }}</div>
                                                        <div class="text-sm text-gray-500">{{ $recipient->opened_at->format('M j, Y g:i A') }}</div>
                                                    @else
                                                        <span class="text-sm text-gray-500">Not opened</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($recipient->clicked_at)
                                                        <div class="text-sm text-gray-900">{{ $recipient->clicked_at->diffForHumans() }}</div>
                                                        <div class="text-sm text-gray-500">{{ $recipient->clicked_at->format('M j, Y g:i A') }}</div>
                                                    @else
                                                        <span class="text-sm text-gray-500">Not clicked</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $recipient->updated_at->diffForHumans() }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                                    No recipient activity to display yet.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($campaign->recipients()->count() > 10)
                                    <div class="px-6 py-3 bg-gray-50 text-right text-sm">
                                        <a href="#" class="text-blue-600 hover:text-blue-900 font-medium">View all recipients ({{ number_format($campaign->recipients()->count()) }})</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($campaign->isSent())
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.min.js"></script>
<script>
function campaignData() {
    return {
        init() {
            this.initEngagementChart();
            this.initDistributionCharts();
        },
        initEngagementChart() {
            @if($campaign->isSent() && count($timeline) > 0)
            const options = {
                series: [{
                    name: 'Opens',
                    data: @json(array_column($timeline, 'opens')),
                    color: '#3B82F6'
                }, {
                    name: 'Clicks',
                    data: @json(array_column($timeline, 'clicks')),
                    color: '#8B5CF6'
                }],
                chart: {
                    height: '100%',
                    type: 'area',
                    fontFamily: 'Inter, sans-serif',
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: false,
                            reset: true
                        }
                    },
                    zoom: {
                        enabled: true
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                xaxis: {
                    type: 'datetime',
                    categories: @json(array_column($timeline, 'date')),
                    labels: {
                        format: 'MMM dd',
                        style: {
                            colors: '#6B7280',
                            fontSize: '12px',
                            fontFamily: 'Inter, sans-serif',
                        },
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: ['#6B7280'],
                            fontSize: '12px',
                            fontFamily: 'Inter, sans-serif',
                        },
                        formatter: function(value) {
                            return Math.round(value);
                        }
                    },
                    min: 0,
                    tickAmount: 5
                },
                tooltip: {
                    enabled: true,
                    x: {
                        format: 'MMM dd, yyyy'
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    offsetY: 0,
                    itemMargin: {
                        horizontal: 10,
                        vertical: 5
                    }
                },
                grid: {
                    borderColor: '#F3F4F6',
                    strokeDashArray: 4,
                    yaxis: {
                        lines: {
                            show: true
                        }
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.1,
                        stops: [0, 90, 100]
                    }
                }
            };

            const chart = new ApexCharts(document.querySelector("#engagement-timeline"), options);
            chart.render();

            // Cleanup on component destroy
            this.$watch('$store.sidebar.isOpen', () => {
                setTimeout(() => {
                    chart.updateOptions({
                        chart: {
                            width: '100%'
                        }
                    });
                }, 300);
            });
            @endif
        },
        initDistributionCharts() {
            @if($campaign->isSent() && count($devices) > 0)
            // Device Distribution Chart
            const deviceOptions = {
                series: @json(array_values($devices)),
                labels: @json(array_keys($devices)),
                chart: {
                    type: 'donut',
                    height: '100%',
                    fontFamily: 'Inter, sans-serif',
                },
                colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'],
                legend: {
                    position: 'right',
                    offsetY: 0,
                    height: 230,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    showAlways: false,
                                    label: 'Total',
                                    fontSize: '14px',
                                    fontWeight: 600,
                                    color: '#6B7280',
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                tooltip: {
                    enabled: true,
                    y: {
                        formatter: function(value, { seriesIndex, w }) {
                            const total = w.config.series.reduce((a, b) => a + b, 0);
                            const percent = Math.round((value / total) * 100);
                            return `${value} (${percent}%)`;
                        }
                    }
                },
                responsive: [{
                    breakpoint: 640,
                    options: {
                        chart: {
                            width: '100%',
                            height: '300px'
                        },
                        legend: {
                            position: 'bottom',
                            horizontalAlign: 'center'
                        }
                    }
                }]
            };

            const deviceChart = new ApexCharts(document.querySelector("#device-distribution"), deviceOptions);
            deviceChart.render();
            @endif

            @if($campaign->isSent() && count($platforms) > 0)
            // Platform Distribution Chart
            const platformOptions = {
                series: @json(array_values($platforms)),
                labels: @json(array_keys($platforms)),
                chart: {
                    type: 'pie',
                    height: '100%',
                    fontFamily: 'Inter, sans-serif',
                },
                colors: ['#8B5CF6', '#EC4899', '#3B82F6', '#10B981', '#F59E0B'],
                legend: {
                    position: 'right',
                    offsetY: 0,
                    height: 230,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    showAlways: false,
                                    label: 'Total',
                                    fontSize: '14px',
                                    fontWeight: 600,
                                    color: '#6B7280',
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val, { seriesIndex, w }) {
                        return w.config.labels[seriesIndex] + ': ' + w.config.series[seriesIndex];
                    },
                    dropShadow: {
                        enabled: false
                    }
                },
                tooltip: {
                    enabled: true,
                    y: {
                        formatter: function(value, { seriesIndex, w }) {
                            const total = w.config.series.reduce((a, b) => a + b, 0);
                            const percent = Math.round((value / total) * 100);
                            return `${value} (${percent}%)`;
                        }
                    }
                },
                responsive: [{
                    breakpoint: 640,
                    options: {
                        chart: {
                            width: '100%',
                            height: '300px'
                        },
                        legend: {
                            position: 'bottom',
                            horizontalAlign: 'center'
                        }
                    }
                }]
            };

            const platformChart = new ApexCharts(document.querySelector("#platform-distribution"), platformOptions);
            platformChart.render();
            @endif
        }
    };
}
</script>

<script>
    // Auto-refresh the page every 60 seconds to update stats
    setTimeout(function() {
        window.location.reload();
    }, 60000);
</script>
@endpush
@endif
@endsection
