@extends('layouts.app-content')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-primary-100 to-secondary-100">
    <div class="bg-primary-700 text-white text-center py-3">
        <div class="container mx-auto">
            <p class="font-medium">Book an appointment without creating an account. <span class="font-bold">Already have an account?</span> <a href="{{ route('login') }}" class="underline text-accent-200 hover:text-accent-600">Login here</a>.</p>
        </div>
    </div>
    
    <main class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-6 text-center">Find a Spa or Salon Near You</h1>
                
                <div id="booking-app" class="guest-booking-form">
                    <div class="text-center mb-8">
                        <p class="text-lg text-gray-600 mb-6">Enter your zip code to find spas and salons in your area</p>
                        
                        <div class="max-w-md mx-auto">
                            <form id="zip-search-form" class="flex items-center">
                                <div class="relative flex-grow">
                                    <input type="text" id="zip-code" name="zip_code" placeholder="Enter zip code" 
                                        class="w-full py-3 px-4 rounded-l-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" 
                                        required pattern="[0-9]{5}" maxlength="5">
                                </div>
                                <button type="submit" class="bg-primary-600 text-white py-3 px-6 rounded-r-lg font-medium hover:bg-primary-700 transition-colors">
                                    <span>Find Locations</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block ml-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </form>
                            <p class="text-sm text-gray-500 mt-2">Example: 90210</p>
                        </div>
                    </div>
                    
                    <div id="search-results" class="hidden">
                        <h2 class="text-xl font-semibold mb-4">Locations Near You</h2>
                        <div id="location-results" class="space-y-4">
                            <!-- Location results will be populated here -->
                        </div>
                        
                        <div id="no-results-message" class="text-center py-8 hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">No locations found</h3>
                            <p class="text-gray-600">We couldn't find any spas or salons in your area.</p>
                            <p class="text-gray-600 mt-2">Try a different zip code or expand your search area.</p>
                        </div>
                    </div>
                    
                    <div id="search-loading" class="text-center py-8 hidden">
                        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-600 mx-auto mb-4"></div>
                        <p class="text-gray-600">Searching for locations near you...</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const zipSearchForm = document.getElementById('zip-search-form');
    const zipCodeInput = document.getElementById('zip-code');
    const searchResults = document.getElementById('search-results');
    const locationResults = document.getElementById('location-results');
    const noResultsMessage = document.getElementById('no-results-message');
    const searchLoading = document.getElementById('search-loading');
    
    // Handle zip code search form submission
    zipSearchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const zipCode = zipCodeInput.value.trim();
        if (!zipCode) return;
        
        // Show loading state
        searchResults.classList.add('hidden');
        searchLoading.classList.remove('hidden');
        
        // Make API call to search for locations
        fetch('{{ route("guest.booking.search") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                zip_code: zipCode
            })
        })
        .then(response => response.json())
        .then(data => {
            // Hide loading state
            searchLoading.classList.add('hidden');
            searchResults.classList.remove('hidden');
            
            if (data.success && data.locations && data.locations.length > 0) {
                displayLocationResults(data.locations);
                noResultsMessage.classList.add('hidden');
            } else {
                locationResults.innerHTML = '';
                noResultsMessage.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error searching for locations:', error);
            searchLoading.classList.add('hidden');
            searchResults.classList.remove('hidden');
            locationResults.innerHTML = `
                <div class="text-center py-4">
                    <p class="text-red-500">Error searching for locations. Please try again.</p>
                </div>
            `;
            noResultsMessage.classList.add('hidden');
        });
    });
    
    // Function to display location results
    function displayLocationResults(locations) {
        locationResults.innerHTML = '';
        
        locations.forEach(location => {
            const locationCard = document.createElement('div');
            locationCard.className = 'bg-gray-50 rounded-lg p-5 hover:shadow-md transition-shadow';
            locationCard.innerHTML = `
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-bold text-lg text-gray-900">${location.name}</h3>
                        <p class="text-gray-600 text-sm">${location.company_name}</p>
                        <p class="text-gray-600 mt-1">${location.address}</p>
                        <p class="text-gray-600">${location.city}, ${location.state} ${location.postal_code}</p>
                    </div>
                    <a href="{{ url('guest-booking') }}/${location.id}" class="bg-primary-600 text-white py-2 px-4 rounded font-medium hover:bg-primary-700 transition-colors">
                        Book Here
                    </a>
                </div>
            `;
            
            locationResults.appendChild(locationCard);
        });
    }
});
</script>
@endpush
