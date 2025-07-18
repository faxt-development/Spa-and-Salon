@extends('layouts.admin')

@section('title', 'Support Documentation')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Support Documentation</h1>
        
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4 text-blue-600">Getting Started</h2>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <p class="mb-4">Welcome to Faxtina's support documentation. Here you'll find resources to help you get the most out of your salon management system.</p>
                <p>Bookmark this page for quick access to support resources whenever you need them.</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <div class="bg-blue-50 p-5 rounded-lg border border-blue-100">
                <h3 class="text-lg font-semibold mb-3 text-blue-700">User Guides</h3>
                <ul class="space-y-2 text-gray-700">
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <a href="#" class="hover:text-blue-600 hover:underline">Appointment Management Guide</a>
                    </li>
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <a href="#" class="hover:text-blue-600 hover:underline">Client Management Guide</a>
                    </li>
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <a href="#" class="hover:text-blue-600 hover:underline">Staff Management Guide</a>
                    </li>
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <a href="#" class="hover:text-blue-600 hover:underline">Inventory Management Guide</a>
                    </li>
                </ul>
            </div>
            
            <div class="bg-purple-50 p-5 rounded-lg border border-purple-100">
                <h3 class="text-lg font-semibold mb-3 text-purple-700">Video Tutorials</h3>
                <ul class="space-y-2 text-gray-700">
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-purple-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <a href="#" class="hover:text-purple-600 hover:underline">Getting Started with Faxtina</a>
                    </li>
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-purple-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <a href="#" class="hover:text-purple-600 hover:underline">Setting Up Your First Appointment</a>
                    </li>
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-purple-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <a href="#" class="hover:text-purple-600 hover:underline">Managing Client Records</a>
                    </li>
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-purple-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <a href="#" class="hover:text-purple-600 hover:underline">Running Reports</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4 text-green-600">Frequently Asked Questions</h2>
            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-medium text-gray-800 mb-2">How do I reset my password?</h3>
                    <p class="text-gray-600">You can reset your password by clicking on the "Forgot Password" link on the login page. Follow the instructions sent to your email to create a new password.</p>
                </div>
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-medium text-gray-800 mb-2">How do I add a new staff member?</h3>
                    <p class="text-gray-600">Navigate to the Staff section from your admin dashboard, click "Add New Staff", and fill out the required information. Don't forget to set their permissions and availability.</p>
                </div>
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-medium text-gray-800 mb-2">How do I create a new service?</h3>
                    <p class="text-gray-600">Go to the Services section, click "Add New Service", and enter the service details including name, duration, price, and assign it to staff members who can perform this service.</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Contact Support</h2>
            <p class="mb-4">Need additional help? Our support team is available Monday through Friday, 9am to 5pm EST.</p>
            <div class="flex flex-col md:flex-row gap-4">
                <a href="mailto:support@faxtina.com" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Email Support
                </a>
                <a href="tel:+18005551234" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    Call Support
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
