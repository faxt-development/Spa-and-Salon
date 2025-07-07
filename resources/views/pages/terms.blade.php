@extends('layouts.guest-content')

@section('title', 'Terms of Service - ' . config('app.name'))

@section('content')
<div class="max-w-4xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Terms of Service</h1>
        <p class="text-lg text-gray-600 dark:text-gray-300">Effective: {{ now()->format('F j, Y') }}</p>
    </div>

    <div class="prose dark:prose-invert max-w-none">
        <div class="mb-12">
            <p class="text-lg text-gray-700 dark:text-gray-300 mb-6">
                Welcome to {{ config('app.name') }}. By accessing or using our services, you agree to be bound by these Terms of Service.
                Please read them carefully before using our services.
            </p>
        </div>

        <div class="space-y-8">
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">1. Account Terms</h2>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li>You must be at least 18 years old to use our services.</li>
                    <li>You are responsible for maintaining the security of your account and password.</li>
                    <li>You are responsible for all content posted and activity that occurs under your account.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">2. Payment and Billing</h2>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li>All fees are stated in USD and are exclusive of taxes.</li>
                    <li>You must provide accurate billing information.</li>
                    <li>Fees are non-refundable except as required by law.</li>
                    <li>We may change our prices and will provide notice of price changes.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">3. User Responsibilities</h2>
                <p>You agree not to:</p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li>Use our services for any illegal purpose.</li>
                    <li>Upload or transmit viruses or any other malicious code.</li>
                    <li>Attempt to gain unauthorized access to our systems.</li>
                    <li>Interfere with or disrupt the integrity or performance of our services.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">4. Intellectual Property</h2>
                <p>All content included on our platform, including text, graphics, logos, and software, is the property of {{ config('app.name') }} or its content suppliers and protected by copyright laws.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">5. Termination</h2>
                <p>We may terminate or suspend your account immediately, without prior notice, for conduct that we believe violates these Terms or is harmful to other users, us, or third parties, or for any other reason.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">6. Limitation of Liability</h2>
                <p>In no event shall {{ config('app.name' )}} be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, or other intangible losses.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">7. Changes to Terms</h2>
                <p>We reserve the right to modify these terms at any time. We will provide notice of any changes by posting the new Terms of Service on this page.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">8. Contact Us</h2>
                <p>If you have any questions about these Terms, please contact us at <a href="mailto:info@faxt.com" class="text-primary-600 hover:underline dark:text-primary-400">info@faxt.com</a>.</p>
            </section>
        </div>
    </div>
</div>
@endsection
