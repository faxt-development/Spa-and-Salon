@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Pricing Rules Configuration</h1>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <form action="{{ route('admin.payments.pricing.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Discount Rules</h2>
            
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="enable_discounts" class="form-checkbox h-5 w-5 text-blue-600" id="enable-discounts" checked>
                    <span class="ml-2 text-gray-700">Enable discount rules</span>
                </label>
            </div>
            
            <div id="discount-rules-container">
                <div class="border-b pb-4 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="discount-type-1">
                                Discount Type
                            </label>
                            <select id="discount-type-1" name="pricing_rules[discounts][0][type]" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="discount-value-1">
                                Value
                            </label>
                            <div class="relative">
                                <input id="discount-value-1" name="pricing_rules[discounts][0][value]" type="number" min="0" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="10">
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                    <span id="discount-symbol-1">%</span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="discount-code-1">
                                Discount Code (Optional)
                            </label>
                            <input id="discount-code-1" name="pricing_rules[discounts][0][code]" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="WELCOME10">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="discount-min-amount-1">
                                Minimum Purchase Amount (Optional)
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500">$</span>
                                </div>
                                <input id="discount-min-amount-1" name="pricing_rules[discounts][0][min_amount]" type="number" min="0" step="0.01" class="shadow appearance-none border rounded w-full py-2 pl-8 pr-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="50.00">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="discount-expiry-1">
                                Expiry Date (Optional)
                            </label>
                            <input id="discount-expiry-1" name="pricing_rules[discounts][0][expiry]" type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="button" class="text-red-600 hover:text-red-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <button type="button" id="add-discount-rule" class="flex items-center text-blue-600 hover:text-blue-800">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Another Discount Rule
                </button>
            </div>
        </div>
        
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Package Deals</h2>
            
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="enable_packages" class="form-checkbox h-5 w-5 text-blue-600" id="enable-packages" checked>
                    <span class="ml-2 text-gray-700">Enable package deals</span>
                </label>
                <p class="text-gray-500 text-sm mt-1 ml-7">Package deals allow customers to save when booking multiple services together.</p>
            </div>
            
            <div id="package-deals-container">
                <div class="border-b pb-4 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="package-name-1">
                                Package Name
                            </label>
                            <input id="package-name-1" name="pricing_rules[packages][0][name]" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Spa Day Package">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="package-discount-1">
                                Discount Percentage
                            </label>
                            <div class="relative">
                                <input id="package-discount-1" name="pricing_rules[packages][0][discount]" type="number" min="0" max="100" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="15">
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                    <span>%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Required Services (minimum 2)
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <div class="flex items-center bg-gray-100 rounded px-3 py-1">
                                <span>Any Massage</span>
                                <button type="button" class="ml-2 text-red-600 hover:text-red-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="flex items-center bg-gray-100 rounded px-3 py-1">
                                <span>Any Facial</span>
                                <button type="button" class="ml-2 text-red-600 hover:text-red-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <button type="button" class="flex items-center bg-blue-100 text-blue-700 rounded px-3 py-1 hover:bg-blue-200">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Service
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="button" class="text-red-600 hover:text-red-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <button type="button" id="add-package-deal" class="flex items-center text-blue-600 hover:text-blue-800">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Another Package Deal
                </button>
            </div>
        </div>
        
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Loyalty Program</h2>
            
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="enable_loyalty" class="form-checkbox h-5 w-5 text-blue-600" id="enable-loyalty">
                    <span class="ml-2 text-gray-700">Enable loyalty program</span>
                </label>
                <p class="text-gray-500 text-sm mt-1 ml-7">Reward your regular customers with points for each purchase.</p>
            </div>
            
            <div id="loyalty-settings" class="hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="points-per-dollar">
                            Points Per Dollar Spent
                        </label>
                        <input id="points-per-dollar" name="pricing_rules[loyalty][points_per_dollar]" type="number" min="0" step="0.1" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="1">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="points-redemption-value">
                            Points Redemption Value ($ per 100 points)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <span class="text-gray-500">$</span>
                            </div>
                            <input id="points-redemption-value" name="pricing_rules[loyalty][redemption_value]" type="number" min="0" step="0.01" class="shadow appearance-none border rounded w-full py-2 pl-8 pr-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="5.00">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex justify-end">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Save Pricing Rules
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle loyalty settings visibility
        const enableLoyaltyCheckbox = document.getElementById('enable-loyalty');
        const loyaltySettings = document.getElementById('loyalty-settings');
        
        enableLoyaltyCheckbox.addEventListener('change', function() {
            loyaltySettings.classList.toggle('hidden', !this.checked);
        });
        
        // Toggle discount symbol based on discount type
        const discountType = document.getElementById('discount-type-1');
        const discountSymbol = document.getElementById('discount-symbol-1');
        
        discountType.addEventListener('change', function() {
            discountSymbol.textContent = this.value === 'percentage' ? '%' : '$';
        });
    });
</script>
@endpush
@endsection
