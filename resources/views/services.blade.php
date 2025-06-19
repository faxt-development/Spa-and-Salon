<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Services') }}
        </h2>
    </x-slot>
<div class="py-16 bg-white dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Our Services</h1>
            <p class="text-xl text-gray-600 dark:text-gray-300">Explore our wide range of professional services</p>
            <div class="mt-8">
                <a href="#services-list" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                    View All Services
                    <svg class="ml-2 -mr-1 w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
            
            @if(isset($categories) && count($categories) > 0)
            <div class="mt-8">
                <div class="flex flex-wrap justify-center gap-2">
                    @foreach($categories as $category)
                        <a href="#category-{{ $category['id'] }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-full text-sm font-medium hover:bg-purple-100 dark:hover:bg-purple-900 transition-colors">
                            {{ $category['name'] }}
                            <span class="ml-1 text-xs bg-purple-600 text-white rounded-full px-2 py-0.5">{{ $category['services_count'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        @if(isset($error))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded" role="alert">
                <p class="font-bold">Error</p>
                <p>{{ $error }}</p>
            </div>
        @endif

        <div id="services-list" class="space-y-16">
            @forelse($groupedServices as $category => $services)
                <div id="category-{{ $services->first()['category']['id'] ?? '' }}" class="category-group">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8 pb-2 border-b border-gray-200 dark:border-gray-700">
                        {{ $category }}
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($services as $service)
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden transition-all hover:shadow-lg hover:-translate-y-1">
                                @if(isset($service['image_url']))
                                    <img src="{{ $service['image_url'] }}" alt="{{ $service['name'] }}" class="w-full h-48 object-cover">
                                @endif
                                <div class="p-6">
                                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                        {{ $service['name'] ?? 'Service' }}
                                    </h3>
                                    @if(!empty($service['description']))
                                        <p class="text-gray-600 dark:text-gray-300 mb-4">{{ $service['description'] }}</p>
                                    @endif
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-bold text-purple-600 dark:text-purple-400">
                                            ${{ number_format($service['price'] / 100, 2) }}
                                        </span>
                                        @if(isset($service['duration']) && $service['duration'] > 0)
                                            <span class="text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">
                                                {{ $service['duration'] }} mins
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <p class="text-gray-600 dark:text-gray-300">No services available at the moment. Please check back later.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-16 text-center">
            <a href="{{ route('home') }}#contact" class="inline-flex items-center px-8 py-4 border border-transparent text-lg font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 transition-colors">
                Book an Appointment
                <svg class="ml-2 -mr-1 w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
</script>
@endpush
</x-app-layout>
