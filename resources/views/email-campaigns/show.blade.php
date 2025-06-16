@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Header with back button and actions -->
                <div class="md:flex md:items-center md:justify-between mb-6">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center">
                            <a href="{{ route('email-campaigns.index') }}" class="mr-4 text-gray-400 hover:text-gray-500">
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
                @if($campaign->isSent())
                <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Campaign Performance</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">Overview of how your email campaign is performing</p>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                        <dl class="sm:divide-y sm:divide-gray-200">
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Recipients</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ number_format($campaign->sent_count) }} sent
                                    @if($campaign->failed_count > 0)
                                        <span class="text-red-600 ml-2">({{ number_format($campaign->failed_count) }} failed)</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Open Rate</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 mr-4">
                                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $campaign->open_rate }}%"></div>
                                        </div>
                                        <span>{{ number_format($campaign->open_rate, 1) }}% ({{ number_format($campaign->opened_count) }} opened)</span>
                                    </div>
                                </dd>
                            </div>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Click Rate</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 mr-4">
                                            <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $campaign->click_rate }}%"></div>
                                        </div>
                                        <span>{{ number_format($campaign->click_rate, 1) }}% ({{ number_format($campaign->clicked_count) }} clicked)</span>
                                    </div>
                                </dd>
                            </div>
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
<script>
    // Auto-refresh the page every 60 seconds to update stats
    setTimeout(function() {
        window.location.reload();
    }, 60000);
</script>
@endpush
@endif
@endsection
