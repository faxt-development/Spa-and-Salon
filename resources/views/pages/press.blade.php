@extends('layouts.guest-content')

@section('title', 'Press - ' . config('app.name'))

@section('content')
<div class="max-w-5xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Press & Media</h1>
        <p class="text-xl text-gray-600 dark:text-gray-300">Latest news and resources for journalists</p>
    </div>

    <div class="prose dark:prose-invert max-w-none">
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8 mb-12">
            <div class="text-center">
                <div class="mx-auto h-24 w-24 text-primary-500 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Stay Tuned</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    We're just getting started! Check back soon for press releases, media assets, and company news.
                </p>
                <p class="text-gray-500 dark:text-gray-400 text-sm">
                    For press inquiries, please contact us at <a href="mailto:info@faxt.com" class="text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">info@faxt.com</a>
                </p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            <!-- Press Kit -->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0 bg-primary-100 dark:bg-primary-900 p-3 rounded-lg">
                            <svg class="h-8 w-8 text-primary-600 dark:text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-medium text-gray-900 dark:text-white">Media Assets</h3>
                    </div>
                    <p class="mt-2 text-gray-600 dark:text-gray-300">
                        Download our logo, product screenshots, and other brand assets for media use.
                    </p>
                    <div class="mt-4">
                        <a href="#" class="inline-flex items-center text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                            Download Press Kit
                            <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0 bg-primary-100 dark:bg-primary-900 p-3 rounded-lg">
                            <svg class="h-8 w-8 text-primary-600 dark:text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-medium text-gray-900 dark:text-white">Press Contact</h3>
                    </div>
                    <div class="mt-2 space-y-2 text-gray-600 dark:text-gray-300">
                        <p class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="ml-3">info@faxt.com</span>
                        </p>
                        <p class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span class="ml-3">+1 (386) 361-7935</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Press Releases -->
        <div class="mt-16">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Press Releases</h2>
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                <div class="p-6">
                    <p class="text-gray-500 dark:text-gray-400 italic">No press releases available at this time.</p>
                </div>
            </div>
        </div>

        <!-- In the News -->
        <div class="mt-16">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">In the News</h2>
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                <div class="p-6">
                    <p class="text-gray-500 dark:text-gray-400 italic">No news articles available at this time.</p>
                </div>
            </div>
        </div>

        <!-- Media Coverage -->
        <div class="mt-16">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Media Coverage</h2>
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                <div class="p-6">
                    <p class="text-gray-500 dark:text-gray-400 italic">No media coverage available at this time.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="bg-primary-700 mt-16">
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
            <span class="block">Ready to learn more?</span>
            <span class="block">Get in touch with our press team.</span>
        </h2>
        <div class="mt-8 flex justify-center">
            <div class="inline-flex rounded-md shadow">
                <a href="mailto:info@faxt.com" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-primary-600 bg-white hover:bg-gray-50">
                    Contact Press Team
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
