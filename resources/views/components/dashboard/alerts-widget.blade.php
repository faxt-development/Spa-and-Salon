@props(['alerts'])

<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900">Alerts</h3>
    </div>
    <div class="px-4 py-5 sm:p-6 space-y-4">
        @forelse($alerts as $alert)
            <div class="rounded-md p-4 {{ $alert['type'] === 'warning' ? 'bg-yellow-50' : 'bg-red-50' }}">
                <div class="flex">
                    <div class="flex-shrink-0">
                        @if($alert['type'] === 'warning')
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium {{ $alert['type'] === 'warning' ? 'text-yellow-800' : 'text-red-800' }}">
                            {{ $alert['title'] }}
                        </h3>
                        <div class="mt-1 text-sm {{ $alert['type'] === 'warning' ? 'text-yellow-700' : 'text-red-700' }}">
                            <p>{{ $alert['message'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500 text-center py-4">No alerts to display.</p>
        @endforelse
    </div>
</div>
