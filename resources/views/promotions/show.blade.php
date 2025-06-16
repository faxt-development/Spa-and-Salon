@extends('layouts.app')

@section('content')
<div x-data="promotionShow()" class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    {{ $promotion->name }}
                </h2>
                <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        <!-- Heroicon name: solid/tag -->
                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                        {{ $promotion->code }}
                    </div>
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        <!-- Heroicon name: solid/clock -->
                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                        @if($promotion->starts_at && $promotion->ends_at)
                            {{ $promotion->starts_at->format('M j, Y') }} - {{ $promotion->ends_at->format('M j, Y') }}
                        @elseif($promotion->starts_at)
                            Starts {{ $promotion->starts_at->format('M j, Y') }}
                        @elseif($promotion->ends_at)
                            Ends {{ $promotion->ends_at->format('M j, Y') }}
                        @else
                            No date range set
                        @endif
                    </div>
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        <!-- Heroicon name: solid/users -->
                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0116 8V7c0-1.3-.416-2.503-1.12-3.485A4.98 4.98 0 0014 3H6a4.98 4.98 0 00-.88 1.515A4.98 4.98 0 004 7v1a5 5 0 004.5 4.97A6.969 6.969 0 007 16c0 .34.024.673.07 1h5.86z" />
                        </svg>
                        {{ $stats['total_usage'] ?? 0 }} {{ Str::plural('use', $stats['total_usage'] ?? 0) }}
                    </div>
                </div>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('promotions.edit', $promotion) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Edit
                </a>
                <button 
                    @click="confirmDelete()" 
                    class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Delete
                </button>
            </div>
        </div>

        <div class="mt-8">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Status Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                @if($promotion->isActive())
                                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                @else
                                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                @endif
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Status</dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900">
                                            @if($promotion->isActive())
                                                <span class="text-green-600">Active</span>
                                            @else
                                                <span class="text-red-600">Inactive</span>
                                            @endif
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usage Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Usage</dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900">
                                            {{ $stats['total_usage'] ?? 0 }}
                                            @if($promotion->usage_limit)
                                                / {{ $promotion->usage_limit }}
                                            @else
                                                / ∞
                                            @endif
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Discount Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        @switch($promotion->type)
                                            @case('percentage')
                                                Discount
                                                @break
                                            @case('fixed')
                                                Fixed Discount
                                                @break
                                            @case('bogo')
                                                Buy One Get One
                                                @break
                                            @case('package')
                                                Package Deal
                                                @break
                                        @endswitch
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900">
                                            @switch($promotion->type)
                                                @case('percentage')
                                                    {{ number_format($promotion->value, 2) }}%
                                                    @break
                                                @case('fixed')
                                                    ${{ number_format($promotion->value, 2) }}
                                                    @break
                                                @case('bogo')
                                                    Buy 1 Get 1
                                                    @break
                                                @case('package')
                                                    ${{ number_format($promotion->value, 2) }} package
                                                    @break
                                            @endswitch
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description Section -->
            @if($promotion->description)
                <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Description</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                        <div class="py-4 sm:py-5 sm:px-6">
                            <p class="text-sm text-gray-900">{{ $promotion->description }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Usage History -->
            <div class="mt-8">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Usage</h3>
                @if($promotion->usages->count() > 0)
                    <div class="flex flex-col">
                        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Customer
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Order
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Discount
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Date
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($promotion->usages as $usage)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $usage->user->name ?? 'Guest' }}
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            {{ $usage->user->email ?? '' }}
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        #{{ $usage->booking_id }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        ${{ number_format($usage->discount_amount, 2) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $usage->created_at->format('M j, Y g:i A') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12 bg-white shadow sm:rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No usage history</h3>
                        <p class="mt-1 text-sm text-gray-500">This promotion hasn't been used yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function promotionShow() {
        return {
            showDeleteModal: false,
            deleting: false,
            
            confirmDelete() {
                this.$dispatch('open-modal', { 
                    component: 'confirm-delete', 
                    props: { 
                        url: '{{ route('promotions.destroy', $promotion) }}',
                        title: 'Delete Promotion',
                        message: 'Are you sure you want to delete this promotion? This action cannot be undone.',
                        method: 'DELETE'
                    } 
                });
            },
            
            // Format date for display
            formatDate(dateString) {
                if (!dateString) return 'N/A';
                const options = { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                };
                return new Date(dateString).toLocaleDateString(undefined, options);
            },
            
            // Format currency
            formatCurrency(amount) {
                if (amount === null || amount === '') return '$0.00';
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    minimumFractionDigits: 2
                }).format(amount);
            }
        };
    }
</script>
@endpush

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .status-badge {
        @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
    }
    .status-active {
        @apply bg-green-100 text-green-800;
    }
    .status-scheduled {
        @apply bg-yellow-100 text-yellow-800;
    }
    .status-expired {
        @apply bg-red-100 text-red-800;
    }
    .status-inactive {
        @apply bg-gray-100 text-gray-800;
    }
</style>
@endpush
