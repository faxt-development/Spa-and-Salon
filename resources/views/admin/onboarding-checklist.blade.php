@extends('layouts.app-content')

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
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('profile.edit') }}" class="hover:text-blue-500 hover:underline">Complete your admin profile information</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('profile.edit') }}" class="hover:text-blue-500 hover:underline">Configure notification preferences</a>
                    </li>
                </ul>
            </div>

            <!-- Step 2 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">2. Business Configuration</h2>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.locations.index') }}" class="hover:text-blue-500 hover:underline">Set up locations and timezones</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.locations.index') }}" class="hover:text-blue-500 hover:underline">Set up business hours</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.appointments.settings') }}" class="hover:text-blue-500 hover:underline">Configure appointment settings</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.services.categories') }}" class="hover:text-blue-500 hover:underline">Set up service categories</a>
                    </li>
                </ul>
            </div>

            <!-- Step 3 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">3. Staff Management</h2>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.staff.index') }}" class="hover:text-blue-500 hover:underline">Add and manage staff members</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.staff.availability') }}" class="hover:text-blue-500 hover:underline">Set staff availability</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.staff.services') }}" class="hover:text-blue-500 hover:underline">Assign services to staff</a>
                    </li>
                </ul>
            </div>

            <!-- Step 4 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">4. Services Setup</h2>
                <ul class="space-y-2 text-gray-600">
                <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.services') }}" class="hover:text-blue-500 hover:underline">Set pricing and durations</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.services.categories') }}" class="hover:text-blue-500 hover:underline">Configure service categories</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.services.packages') }}" class="hover:text-blue-500 hover:underline">Create service packages</a>
                    </li>
                </ul>
            </div>

            <!-- Step 5 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">5. Appointment Management</h2>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.appointments.learn') }}" class="hover:text-blue-500 hover:underline">Learn to schedule appointments</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.appointments.reminders') }}" class="hover:text-blue-500 hover:underline">Set up appointment reminders</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.appointments.policies') }}" class="hover:text-blue-500 hover:underline">Configure cancellation policies</a>
                    </li>
                </ul>
            </div>

            <!-- Step 6 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">6. Email Campaigns</h2>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.email.welcome') }}" class="hover:text-blue-500 hover:underline">Create welcome emails</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.email.reminders') }}" class="hover:text-blue-500 hover:underline">Set up appointment reminders</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.email.campaigns') }}" class="hover:text-blue-500 hover:underline">Configure marketing campaigns</a>
                    </li>
                </ul>
            </div>

            <!-- Step 7 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">7. Payment Processing</h2>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.payments.methods') }}" class="hover:text-blue-500 hover:underline">Configure payment methods</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.payments.pricing') }}" class="hover:text-blue-500 hover:underline">Set up pricing rules</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.payments.tax') }}" class="hover:text-blue-500 hover:underline">Configure tax settings</a>
                    </li>
                </ul>
            </div>

            <!-- Step 8 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">8. Reports & Analytics</h2>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.reports.custom') }}" class="hover:text-blue-500 hover:underline">Set up custom reports</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.analytics') }}" class="hover:text-blue-500 hover:underline">Configure analytics dashboard</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.reports.metrics') }}" class="hover:text-blue-500 hover:underline">Set up performance metrics</a>
                    </li>
                </ul>
            </div>

            <!-- Step 9 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">9. Security & Access</h2>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.security.roles') }}" class="hover:text-blue-500 hover:underline">Configure user roles</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.security.access') }}" class="hover:text-blue-500 hover:underline">Set up access controls</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.security.audit') }}" class="hover:text-blue-500 hover:underline">Configure audit logging</a>
                    </li>
                </ul>
            </div>

            <!-- Step 10 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">10. Support & Resources</h2>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.support.docs') }}" class="hover:text-blue-500 hover:underline">Bookmark support documentation</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.support.contacts') }}" class="hover:text-blue-500 hover:underline">Set up emergency contacts</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500">
                        <a href="{{ route('admin.support.backup') }}" class="hover:text-blue-500 hover:underline">Review backup procedures</a>
                    </li>
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
