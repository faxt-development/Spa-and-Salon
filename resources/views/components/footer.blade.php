<footer class="bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 mt-12">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Company Info -->
            <div class="space-y-4">
                <div class="flex items-center">
                    <x-application-logo class="h-8 w-auto" />
                    <span class="ml-2 text-xl font-bold text-gray-900 dark:text-white">Faxtina</span>
                </div>
                <p class="text-gray-600 dark:text-gray-300">
                    Empowering your business with our comprehensive salon and spa management solution.
                </p>
                <div class="flex space-x-4">

                    <a href="https://x.com/faxtina61141" class="text-gray-500 hover:text-primary-500 dark:text-gray-400 dark:hover:text-primary-400">
                        <span class="sr-only">X</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white tracking-wider uppercase">Quick Links</h3>
                <ul class="mt-4 space-y-2">
                    <li><a href="{{ route('home') }}" class="text-base text-gray-600 hover:text-primary-500 dark:text-gray-300 dark:hover:text-primary-400">Home</a></li>
                    <li><a href="{{ route('pricing') }}" class="text-base text-gray-600 hover:text-primary-500 dark:text-gray-300 dark:hover:text-primary-400">Pricing</a></li>
                    <li><a href="{{ route('contact') }}" class="text-base text-gray-600 hover:text-primary-500 dark:text-gray-300 dark:hover:text-primary-400">Contact</a></li>
                    <li><a href="{{ route('login') }}" class="text-base text-gray-600 hover:text-primary-500 dark:text-gray-300 dark:hover:text-primary-400">Login</a></li>
                </ul>
            </div>

            <!-- Resources -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white tracking-wider uppercase">Resources</h3>
                <ul class="mt-4 space-y-2">
                    <li><a href="{{ route('privacy') }}" class="text-base text-gray-600 hover:text-primary-500 dark:text-gray-300 dark:hover:text-primary-400">Privacy Policy</a></li>
                    <li><a href="{{ route('terms') }}" class="text-base text-gray-600 hover:text-primary-500 dark:text-gray-300 dark:hover:text-primary-400">Terms of Service</a></li>
                    <li><a href="{{ route('gdpr') }}" class="text-base text-gray-600 hover:text-primary-500 dark:text-gray-300 dark:hover:text-primary-400">GDPR Compliance</a></li>
                    <li><a href="{{ route('press') }}" class="text-base text-gray-600 hover:text-primary-500 dark:text-gray-300 dark:hover:text-primary-400">Press Kit</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white tracking-wider uppercase">Contact Us</h3>
                <ul class="mt-4 space-y-2">
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="ml-3 text-base text-gray-600 dark:text-gray-300">info@faxt.com</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span class="ml-3 text-base text-gray-600 dark:text-gray-300">+1 (386) 361-7935</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Copyright -->
        <div class="mt-12 border-t border-gray-200 dark:border-gray-700 pt-8">
            <p class="text-base text-gray-500 dark:text-gray-400 text-center">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>
</footer>
