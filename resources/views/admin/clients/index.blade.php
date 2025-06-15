<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Client Management') }}
            </h2>
            <a href="{{ route('admin.clients.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                {{ __('Add New Client') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Search and Filter Section -->
                    <div class="mb-6" x-data="{ showFilters: false }">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="flex-1">
                                <form method="GET" action="{{ route('admin.clients.index') }}" class="flex items-center">
                                    <div class="relative flex-1">
                                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search clients..." 
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <button type="submit" class="absolute right-0 top-0 mt-2 mr-2">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="flex items-center">
                                <button @click="showFilters = !showFilters" class="flex items-center text-sm text-gray-600 hover:text-gray-900">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                    </svg>
                                    {{ __('Filters') }}
                                </button>
                            </div>
                        </div>
                        
                        <!-- Filter Options -->
                        <div x-show="showFilters" x-transition class="mt-4 p-4 bg-gray-50 rounded-md">
                            <form method="GET" action="{{ route('admin.clients.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                
                                <div>
                                    <label for="sort_by" class="block text-sm font-medium text-gray-700">{{ __('Sort By') }}</label>
                                    <select name="sort_by" id="sort_by" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>{{ __('Date Added') }}</option>
                                        <option value="last_name" {{ request('sort_by') == 'last_name' ? 'selected' : '' }}>{{ __('Last Name') }}</option>
                                        <option value="last_visit" {{ request('sort_by') == 'last_visit' ? 'selected' : '' }}>{{ __('Last Visit') }}</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="sort_direction" class="block text-sm font-medium text-gray-700">{{ __('Order') }}</label>
                                    <select name="sort_direction" id="sort_direction" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="desc" {{ request('sort_direction') == 'desc' ? 'selected' : '' }}>{{ __('Descending') }}</option>
                                        <option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>{{ __('Ascending') }}</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="per_page" class="block text-sm font-medium text-gray-700">{{ __('Items Per Page') }}</label>
                                    <select name="per_page" id="per_page" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                    </select>
                                </div>
                                
                                <div class="md:col-span-3 flex justify-end">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                        {{ __('Apply Filters') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Client List -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Name') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Contact') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Last Visit') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($clients as $client)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                    <span class="text-gray-500 font-medium">{{ substr($client->first_name, 0, 1) }}{{ substr($client->last_name, 0, 1) }}</span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <a href="{{ route('admin.clients.show', $client->id) }}" class="hover:underline">
                                                            {{ $client->first_name }} {{ $client->last_name }}
                                                        </a>
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $client->date_of_birth ? $client->date_of_birth->format('M d, Y') : 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $client->email ?? 'N/A' }}</div>
                                            <div class="text-sm text-gray-500">{{ $client->phone ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $client->last_visit ? $client->last_visit->format('M d, Y') : 'Never' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $client->last_visit ? $client->last_visit->diffForHumans() : '' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.clients.show', $client->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ __('View') }}
                                                </a>
                                                <a href="{{ route('admin.clients.edit', $client->id) }}" class="text-yellow-600 hover:text-yellow-900">
                                                    {{ __('Edit') }}
                                                </a>
                                                <form method="POST" action="{{ route('admin.clients.destroy', $client->id) }}" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this client?')">
                                                        {{ __('Delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            {{ __('No clients found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $clients->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
