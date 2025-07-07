@extends('layouts.guest-content')

@section('title', 'Privacy Policy - ' . config('app.name'))

@section('content')
<div class="max-w-4xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Privacy Policy</h1>
        <p class="text-lg text-gray-600 dark:text-gray-300">Last updated: {{ now()->format('F j, Y') }}</p>
    </div>

    <div class="prose dark:prose-invert max-w-none">
        <div class="mb-12">
            <p class="text-lg text-gray-700 dark:text-gray-300 mb-6">
                At {{ config('app.name') }}, we take your privacy seriously. This Privacy Policy explains how we collect, use,
                disclose, and safeguard your information when you use our services.
            </p>
        </div>

        <div class="space-y-8">
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">1. Information We Collect</h2>
                <p>We collect information that you provide directly to us, including:</p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li>Personal information (name, email, phone number) when you create an account.</li>
                    <li>Business information (salon/spa name, address, services offered).</li>
                    <li>Payment information processed through our secure payment processor.</li>
                    <li>Communications and correspondence with our support team.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">2. How We Use Your Information</h2>
                <p>We use the information we collect to:</p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li>Provide, operate, and maintain our services.</li>
                    <li>Process transactions and send related information.</li>
                    <li>Send you technical notices, updates, and support messages.</li>
                    <li>Respond to your comments, questions, and requests.</li>
                    <li>Monitor and analyze usage and trends to improve our services.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">3. Information Sharing</h2>
                <p>We do not sell or rent your personal information. We may share information with:</p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li>Service providers who perform services on our behalf.</li>
                    <li>Business partners to offer you certain products or services.</li>
                    <li>Law enforcement or other government officials, in response to a verified request.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">4. Data Security</h2>
                <p>We implement appropriate technical and organizational measures to protect your personal information. However, no method of transmission over the Internet is 100% secure.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">5. Your Rights</h2>
                <p>You have the right to:</p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li>Access, update, or delete your information.</li>
                    <li>Opt-out of marketing communications.</li>
                    <li>Request a copy of your personal data.</li>
                    <li>Withdraw consent where we rely on it to process your information.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">6. Cookies and Tracking</h2>
                <p>We use cookies and similar tracking technologies to track activity on our service and hold certain information to improve your experience.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">7. Changes to This Policy</h2>
                <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">8. Contact Us</h2>
                <p>If you have any questions about this Privacy Policy, please contact us at <a href="mailto:info@faxt.com" class="text-primary-600 hover:underline dark:text-primary-400">info@faxt.com</a>.</p>
            </section>
        </div>
    </div>
</div>
@endsection
