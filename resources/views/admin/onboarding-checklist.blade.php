@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Admin Onboarding Checklist</h1>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <p class="text-gray-600 mb-8">
            Welcome to your admin dashboard! Follow these 10 essential steps to get started with managing your spa and salon business effectively.
        </p>

        <div class="space-y-6">
            <!-- Step 1 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">1. Profile Setup</h2>
                <ul class="list-disc list-inside text-gray-600">
                    <li>Complete your admin profile information</li>
                    <li>Set up your preferred time zone</li>
                    <li>Configure notification preferences</li>
                </ul>
            </div>

            <!-- Step 2 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">2. Business Configuration</h2>
                <ul class="list-disc list-inside text-gray-600">
                    <li>Set up business hours</li>
                    <li>Configure appointment settings</li>
                    <li>Set up service categories</li>
                </ul>
            </div>

            <!-- Step 3 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">3. Staff Management</h2>
                <ul class="list-disc list-inside text-gray-600">
                    <li>Add and manage staff members</li>
                    <li>Set staff availability</li>
                    <li>Assign services to staff</li>
                </ul>
            </div>

            <!-- Step 4 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">4. Services Setup</h2>
                <ul class="list-disc list-inside text-gray-600">
                    <li>Create service packages</li>
                    <li>Set pricing and durations</li>
                    <li>Configure service categories</li>
                </ul>
            </div>

            <!-- Step 5 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">5. Appointment Management</h2>
                <ul class="list-disc list-inside text-gray-600">
                    <li>Learn to schedule appointments</li>
                    <li>Set up appointment reminders</li>
                    <li>Configure cancellation policies</li>
                </ul>
            </div>

            <!-- Step 6 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">6. Email Campaigns</h2>
                <ul class="list-disc list-inside text-gray-600">
                    <li>Create welcome emails</li>
                    <li>Set up appointment reminders</li>
                    <li>Configure marketing campaigns</li>
                </ul>
            </div>

            <!-- Step 7 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">7. Payment Processing</h2>
                <ul class="list-disc list-inside text-gray-600">
                    <li>Configure payment methods</li>
                    <li>Set up pricing rules</li>
                    <li>Configure tax settings</li>
                </ul>
            </div>

            <!-- Step 8 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">8. Reports & Analytics</h2>
                <ul class="list-disc list-inside text-gray-600">
                    <li>Set up custom reports</li>
                    <li>Configure analytics dashboard</li>
                    <li>Set up performance metrics</li>
                </ul>
            </div>

            <!-- Step 9 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">9. Security & Access</h2>
                <ul class="list-disc list-inside text-gray-600">
                    <li>Configure user roles</li>
                    <li>Set up access controls</li>
                    <li>Configure audit logging</li>
                </ul>
            </div>

            <!-- Step 10 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">10. Support & Resources</h2>
                <ul class="list-disc list-inside text-gray-600">
                    <li>Bookmark support documentation</li>
                    <li>Set up emergency contacts</li>
                    <li>Review backup procedures</li>
                </ul>
            </div>
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('admin.dashboard') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                Start Managing Your Business
            </a>
        </div>
    </div>
</div>
@endsection
