@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Payment Methods Configuration</h1>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Stripe Integration</h2>
        
        @if ($stripeEnabled)
            <div class="flex items-center mb-4">
                <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <span class="text-green-700">Stripe API keys configured</span>
            </div>
            
            @if ($stripeConnected)
                <div class="flex items-center mb-4">
                    <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <span class="text-green-700">Connected to Stripe account</span>
                </div>
            @else
                <div class="flex items-center mb-4">
                    <div class="w-6 h-6 bg-red-500 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <span class="text-red-700">Not connected to Stripe account. Please check your API keys.</span>
                </div>
            @endif
            
            <div class="mt-6">
                <h3 class="font-medium mb-2">Stripe Settings</h3>
                <p class="text-gray-600 mb-4">Your Stripe API keys are configured in your environment file. To update them, edit your .env file or contact your administrator.</p>
                
                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                    <p class="text-sm text-gray-600 mb-2"><strong>Environment Variables:</strong></p>
                    <ul class="list-disc list-inside text-sm text-gray-600">
                        <li>STRIPE_KEY - Your publishable key</li>
                        <li>STRIPE_SECRET - Your secret key</li>
                        <li>STRIPE_WEBHOOK_SECRET - Your webhook signing secret</li>
                    </ul>
                </div>
            </div>
        @else
            <div class="flex items-center mb-4">
                <div class="w-6 h-6 bg-red-500 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <span class="text-red-700">Stripe API keys not configured</span>
            </div>
            
            <div class="mt-4">
                <p class="text-gray-600 mb-4">To enable Stripe payments, you need to add your API keys to your environment file.</p>
                
                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                    <p class="text-sm text-gray-600 mb-2"><strong>Add these to your .env file:</strong></p>
                    <pre class="text-sm bg-gray-100 p-2 rounded">
STRIPE_KEY=your_publishable_key
STRIPE_SECRET=your_secret_key
STRIPE_WEBHOOK_SECRET=your_webhook_secret</pre>
                </div>
                
                <p class="text-gray-600 mt-4">Don't have a Stripe account? <a href="https://dashboard.stripe.com/register" target="_blank" class="text-blue-600 hover:underline">Sign up for free</a>.</p>
            </div>
        @endif
    </div>
    
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Accepted Payment Methods</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="border rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 mr-3" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="24" height="24" rx="4" fill="#635BFF"/>
                            <path d="M13.48 11.67C13.48 12.97 12.43 13.57 11.3 13.57H9.85V9.77H11.3C12.43 9.77 13.48 10.37 13.48 11.67ZM11.24 10.71H10.95V12.63H11.24C11.95 12.63 12.37 12.25 12.37 11.67C12.37 11.09 11.95 10.71 11.24 10.71Z" fill="white"/>
                            <path d="M14.17 13.57H15.27V9.77H14.17V13.57Z" fill="white"/>
                            <path d="M16.5 12.47L18.04 9.77H16.85L15.95 11.37L15.05 9.77H13.86L15.4 12.47V13.57H16.5V12.47Z" fill="white"/>
                            <path d="M8.75 12.62H7.26L8.75 10.28V9.77H6.06V10.71H7.44L6.06 12.95V13.57H8.75V12.62Z" fill="white"/>
                        </svg>
                        <span class="font-medium">Credit & Debit Cards</span>
                    </div>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </label>
                </div>
                <p class="text-gray-600 text-sm">Accept all major credit and debit cards including Visa, Mastercard, American Express, and Discover.</p>
            </div>
            
            <div class="border rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 mr-3" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="24" height="24" rx="4" fill="#009CDE"/>
                            <path d="M7.07 10.08H8.58C9.19 10.08 9.66 10.53 9.66 11.16C9.66 11.79 9.19 12.24 8.58 12.24H7.07V10.08ZM15.98 10.08H17.49C18.1 10.08 18.57 10.53 18.57 11.16C18.57 11.79 18.1 12.24 17.49 12.24H15.98V10.08ZM11.53 10.08H13.04C13.65 10.08 14.12 10.53 14.12 11.16C14.12 11.79 13.65 12.24 13.04 12.24H11.53V10.08Z" fill="white"/>
                            <path d="M7.07 12.76H8.58C9.19 12.76 9.66 13.21 9.66 13.84C9.66 14.47 9.19 14.92 8.58 14.92H7.07V12.76ZM15.98 12.76H17.49C18.1 12.76 18.57 13.21 18.57 13.84C18.57 14.47 18.1 14.92 17.49 14.92H15.98V12.76ZM11.53 12.76H13.04C13.65 12.76 14.12 13.21 14.12 13.84C14.12 14.47 13.65 14.92 13.04 14.92H11.53V12.76Z" fill="white"/>
                        </svg>
                        <span class="font-medium">Digital Wallets</span>
                    </div>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </label>
                </div>
                <p class="text-gray-600 text-sm">Accept payments via Apple Pay, Google Pay, and other digital wallets for faster checkout.</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Payment Processing Settings</h2>
        
        <form>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="currency">
                    Default Currency
                </label>
                <select id="currency" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="usd">USD - United States Dollar</option>
                    <option value="eur">EUR - Euro</option>
                    <option value="gbp">GBP - British Pound</option>
                    <option value="cad">CAD - Canadian Dollar</option>
                    <option value="aud">AUD - Australian Dollar</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600" checked>
                    <span class="ml-2 text-gray-700">Capture payment immediately (recommended)</span>
                </label>
                <p class="text-gray-500 text-sm mt-1 ml-7">If unchecked, payments will be authorized but not captured until manually approved.</p>
            </div>
            
            <div class="flex items-center justify-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
