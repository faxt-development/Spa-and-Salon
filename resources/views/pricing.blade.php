@extends('layouts.app-content')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-primary-100 to-secondary-100">
    <div class="bg-primary-700 text-white text-center py-3">
        <div class="container mx-auto">
            <p class="font-medium">This page is for spa and salon owners looking to manage their business. <span class="font-bold">Not a business owner?</span> <a href="{{ route('guest.booking.index') }}" class="underline text-accent-200 hover:text-accent-600">Click here to book an appointment as a customer</a>.</p>
        </div>
    </div>
    <main class="container mx-auto px-4 py-12 md:py-24">
        <div class="max-w-4xl mx-auto text-center mb-16">
            <div class="bg-primary-100 text-primary-800 py-2 px-4 rounded-lg inline-block mb-6">
                <span class="font-medium">For Spa & Salon Owners</span>
            </div>
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl mb-4">
                Grow Your Spa & Salon Business
            </h1>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">
                Simple, transparent pricing for salon management
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Choose the perfect plan to manage your spa or salon operations. Start with a 30-day free trial. No credit card required. Cancel anytime.
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            @foreach($pricingTiers as $tier)
            <div class="rounded-2xl shadow-xl overflow-hidden {{ $tier['highlight'] ? 'ring-2 ring-primary-500 transform scale-105 z-10 bg-white' : 'bg-white border border-gray-200' }}">
                @if($tier['highlight'])
                <div class="bg-gradient-to-r from-primary-600 to-primary-700 text-white text-center py-2 text-sm font-medium">
                    Most Popular
                </div>
                @endif
                <div class="p-6">
                    @if(isset($tier['image']))
                    <div class="flex justify-center mb-4">
                        <img src="{{ asset($tier['image']) }}" alt="{{ $tier['name'] }} tier" class="h-24 w-auto">
                    </div>
                    @endif
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $tier['name'] }}</h2>
                    <p class="text-gray-600 mb-6">{{ $tier['description'] }}</p>

                    <div class="flex items-baseline mb-6">
                        <span class="text-4xl font-bold text-gray-900">{{ $tier['price'] }}</span>
                        <span class="ml-1 text-gray-500">{{ $tier['period'] }}</span>
                        @if($tier['firstMonthFree'])
                        <span class="ml-2 text-sm bg-primary-100 text-primary-800 px-2 py-1 rounded-full">
                            First month free
                        </span>
                        @endif
                    </div>

                    <button
                        onclick="handleCheckout('{{ $tier['priceId'] }}', {{ $tier['firstMonthFree'] ? 'true' : 'false' }})"
                        class="w-full py-3 px-6 rounded-lg font-medium {{ $tier['highlight'] ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white hover:opacity-90' : 'bg-gray-100 text-gray-900 hover:bg-gray-200' }} transition-all duration-200"
                    >
                        {{ $tier['cta'] }}
                    </button>

                    <ul class="mt-6 space-y-3">
                        @foreach($tier['features'] as $feature)
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-gray-600">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-16 max-w-3xl mx-auto text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Frequently asked questions for salon owners</h2>
            <div class="space-y-4 text-left">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="font-semibold text-lg">Is there a free trial for my salon business?</h3>
                    <p class="text-gray-600 mt-2">Yes! All salon management plans come with a 30-day free trial. No credit card required to start managing your business.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="font-semibold text-lg">Can I change plans later?</h3>
                    <p class="text-gray-600 mt-2">Absolutely! You can upgrade or downgrade your plan at any time.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="font-semibold text-lg">What payment methods do you accept?</h3>
                    <p class="text-gray-600 mt-2">We accept all major credit cards through our secure payment processor, Stripe.</p>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    // Check if there's a priceId in the URL and trigger checkout automatically
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const priceIdParam = urlParams.get('priceId');

        if (priceIdParam) {
            // Find the matching pricing tier
            @foreach($pricingTiers as $tier)
            if ('{{ $tier["priceId"] }}' === priceIdParam || '{{ "price_" . strtolower(str_replace(" ", "_", $tier["name"])) }}' === priceIdParam) {
                // Small delay to allow the page to render first
                setTimeout(() => {
                    handleCheckout('{{ $tier["priceId"] }}', {{ $tier["firstMonthFree"] ? 'true' : 'false' }});
                }, 500);
                return;
            }
            @endforeach
        }
    });

    function handleCheckout(priceId, firstMonthFree) {
        // For development/testing - use actual price IDs from environment variables
        const testPriceMap = {
            "price_self_managed": "{{ env('price_self_managed', '') }}",
            "price_single_location": "{{ env('price_single_location', '') }}",
            "price_multi_location": "{{ env('price_multi_location', '') }}"
        };

        // Use the actual priceId if it's a real Stripe price ID, otherwise use the test price
        const actualPriceId = priceId.startsWith('price_') && priceId.length > 14 ?
            priceId :
            (testPriceMap[priceId] || 'price_1OvXXXXXXXXXXXXXXXXXXXXX');

        console.log('Initiating checkout with price ID:', actualPriceId);

        // Get the current URL for success and cancel URLs
        const currentUrl = window.location.origin;
        // Use the exact format Stripe expects for the session ID placeholder
        const successUrl = currentUrl + '/success?session_id={CHECKOUT_SESSION_ID}';
        const cancelUrl = currentUrl + '/pricing';

        // Call the API endpoint
        fetch('/api/subscriptions/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                priceId: actualPriceId,
                firstMonthFree: firstMonthFree,
                successUrl,
                cancelUrl
            }),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (!data.sessionId) {
                console.error('No session ID returned from checkout API');
                alert('Error creating checkout session. Please try again later.');
                return;
            }

            // Initialize Stripe
            const stripe = Stripe('{{ config('services.stripe.key') }}');

            // Redirect to Checkout
            stripe.redirectToCheckout({ sessionId: data.sessionId })
                .then(function(result) {
                    if (result.error) {
                        console.error('Error redirecting to checkout:', result.error);
                        alert(`Payment error: ${result.error.message}`);
                    }
                });
        })
        .catch(error => {
            console.error('Error creating checkout session:', error);
            alert('An unexpected error occurred. Please try again later.');
        });
    }
</script>
@endpush
