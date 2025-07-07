@extends('layouts.app-content')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">
    <main class="container mx-auto px-4 py-12 md:py-24">
        <div class="max-w-4xl mx-auto text-center mb-16">
            <div class="mb-8 flex justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl mb-4">
                Thank you for your subscription!
            </h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto mb-4">
                Your subscription has been successfully processed. You'll receive a confirmation email shortly.
            </p>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto mb-8">
                <strong>What's next?</strong> Please check your email for important onboarding instructions. We'll guide you through setting up your spa admin account, configuring your business information, and getting started with Faxtina's powerful features.
            </p>
            <div class="mt-8 border-t border-gray-200 pt-8">
                <p class="text-md text-gray-600 max-w-2xl mx-auto">
                    If you have any questions or need assistance, please contact our support team at <a href="mailto:support@faxtina.com" class="text-primary-600 hover:text-primary-800 font-medium">info@faxt.com</a> or call us at <a href="tel:+13863617935" class="text-primary-600 hover:text-primary-800 font-medium">+1 (386) 361-7935</a>.
                </p>
            </div>
        </div>
    </main>
</div>
@endsection
