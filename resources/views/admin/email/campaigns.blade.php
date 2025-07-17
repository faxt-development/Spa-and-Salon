@extends('layouts.admin')

@section('title', 'Marketing Email Campaigns')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Marketing Email Campaigns</h1>
        <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded">
            Back to Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2">
            <div class="bg-white shadow-md rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Your Campaigns</h2>
                    <a href="{{ route('email-campaigns.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                        Create New Campaign
                    </a>
                </div>
                
                @if($campaigns->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipients</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent/Scheduled</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($campaigns as $campaign)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $campaign->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $campaign->status === 'sent' ? 'bg-green-100 text-green-800' : 
                                                   ($campaign->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : 
                                                   'bg-gray-100 text-gray-800') }}">
                                                {{ ucfirst($campaign->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $campaign->recipients_count }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($campaign->status === 'sent')
                                                {{ $campaign->sent_at ? $campaign->sent_at->format('M d, Y H:i') : 'N/A' }}
                                            @elseif($campaign->status === 'scheduled')
                                                {{ $campaign->scheduled_for ? $campaign->scheduled_for->format('M d, Y H:i') : 'N/A' }}
                                            @else
                                                Not sent
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('email-campaigns.show', $campaign->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                            @if($campaign->status === 'draft')
                                                <a href="{{ route('email-campaigns.edit', $campaign->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                            @endif
                                            @if($campaign->status === 'draft' || $campaign->status === 'scheduled')
                                                <form method="POST" action="{{ route('email-campaigns.destroy', $campaign->id) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this campaign?')">Delete</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $campaigns->links() }}
                    </div>
                @else
                    <p class="text-gray-500">No campaigns found. Create your first campaign to get started.</p>
                @endif
            </div>
        </div>
        
        <div class="lg:col-span-1">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Campaign Performance</h2>
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-700">Recent Campaigns</h3>
                        <p class="text-3xl font-bold text-blue-600">{{ $campaigns->where('status', 'sent')->count() }}</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-700">Average Open Rate</h3>
                        <p class="text-3xl font-bold text-blue-600">32%</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-700">Average Click Rate</h3>
                        <p class="text-3xl font-bold text-blue-600">12%</p>
                    </div>
                    <div class="pt-4 border-t border-gray-200">
                        <a href="{{ route('email-marketing.dashboard') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                            View Full Analytics →
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="bg-white shadow-md rounded-lg p-6 mt-6">
                <h2 class="text-xl font-semibold mb-4">Available Segments</h2>
                <ul class="space-y-2">
                    @foreach($segments as $segment)
                        <li class="flex items-center">
                            <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                            <span>{{ $segment['name'] }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('email-campaigns.create') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                        Create Targeted Campaign →
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Email Marketing Best Practices</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-800 mb-2">Subject Lines</h3>
                <p class="text-gray-600">Keep subject lines under 50 characters. Use personalization and action-oriented language to improve open rates.</p>
            </div>
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-800 mb-2">Sending Times</h3>
                <p class="text-gray-600">Tuesday through Thursday mornings (9-11am) and afternoons (1-3pm) typically have the highest engagement rates.</p>
            </div>
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-800 mb-2">Content Tips</h3>
                <p class="text-gray-600">Use a single, clear call-to-action. Include both text and image content, and always test your emails before sending.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Campaign page initialized');
    });
</script>
@endpush
@endsection
