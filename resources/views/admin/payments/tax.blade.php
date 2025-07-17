@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Tax Settings Configuration</h1>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <form action="{{ route('admin.payments.tax.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">General Tax Settings</h2>
            
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="tax_enabled" class="form-checkbox h-5 w-5 text-blue-600" id="enable-tax" checked>
                    <span class="ml-2 text-gray-700">Enable tax collection</span>
                </label>
                <p class="text-gray-500 text-sm mt-1 ml-7">When enabled, tax will be calculated and added to applicable transactions.</p>
            </div>
            
            <div id="tax-settings">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="tax-name">
                            Tax Name
                        </label>
                        <input id="tax-name" name="tax_name" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Sales Tax" value="Sales Tax">
                        <p class="text-gray-500 text-xs mt-1">This name will appear on receipts and invoices.</p>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="tax-rate">
                            Default Tax Rate (%)
                        </label>
                        <div class="relative">
                            <input id="tax-rate" name="tax_rate" type="number" min="0" max="100" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="7.5">
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                <span>%</span>
                            </div>
                        </div>
                        <p class="text-gray-500 text-xs mt-1">This rate will be applied to all taxable items.</p>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="tax_included_in_prices" class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2 text-gray-700">Prices include tax</span>
                    </label>
                    <p class="text-gray-500 text-sm mt-1 ml-7">When enabled, the displayed prices will include tax. Otherwise, tax will be added at checkout.</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Location-Based Tax Rates</h2>
            
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="enable_location_tax" class="form-checkbox h-5 w-5 text-blue-600" id="enable-location-tax">
                    <span class="ml-2 text-gray-700">Enable location-based tax rates</span>
                </label>
                <p class="text-gray-500 text-sm mt-1 ml-7">When enabled, tax rates will be determined by the customer's location.</p>
            </div>
            
            <div id="location-tax-settings" class="hidden">
                <div class="mb-4 border-b pb-4">
                    <h3 class="text-lg font-medium mb-3">Location Tax Rules</h3>
                    
                    <div id="tax-rules-container">
                        <div class="border rounded-lg p-4 mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="tax-location-1">
                                        Location
                                    </label>
                                    <input id="tax-location-1" name="tax_rules[0][location]" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="New York">
                                </div>
                                
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="tax-rate-1">
                                        Tax Rate (%)
                                    </label>
                                    <div class="relative">
                                        <input id="tax-rate-1" name="tax_rules[0][rate]" type="number" min="0" max="100" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="8.875">
                                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                            <span>%</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="tax-name-1">
                                        Tax Name (Optional)
                                    </label>
                                    <input id="tax-name-1" name="tax_rules[0][name]" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="NY Sales Tax">
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
                    </div>
                    
                    <button type="button" id="add-tax-rule" class="flex items-center text-blue-600 hover:text-blue-800">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Another Tax Rule
                    </button>
                </div>
            </div>
        </div>
        
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Tax Exemptions</h2>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Tax-Exempt Categories
                </label>
                <p class="text-gray-500 text-sm mb-3">Select service or product categories that should be exempt from tax.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <label class="flex items-center">
                        <input type="checkbox" name="tax_exempt_categories[]" value="gift_cards" class="form-checkbox h-5 w-5 text-blue-600" checked>
                        <span class="ml-2 text-gray-700">Gift Cards</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="tax_exempt_categories[]" value="memberships" class="form-checkbox h-5 w-5 text-blue-600" checked>
                        <span class="ml-2 text-gray-700">Memberships</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="tax_exempt_categories[]" value="medical_services" class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2 text-gray-700">Medical Services</span>
                    </label>
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Tax Exemption Handling
                </label>
                <div class="mt-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="allow_customer_exemptions" class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2 text-gray-700">Allow customers to claim tax exemption status</span>
                    </label>
                    <p class="text-gray-500 text-sm mt-1 ml-7">When enabled, customers can provide tax exemption certificates during checkout.</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Tax Reporting</h2>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Tax Identification Number (Optional)
                </label>
                <input name="tax_id" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter your business tax ID">
                <p class="text-gray-500 text-xs mt-1">This will appear on tax reports and may be required for tax filing.</p>
            </div>
            
            <div class="mb-6">
                <p class="text-gray-700 mb-2">Tax reports are available in the reporting section:</p>
                <a href="{{ route('admin.reports.tax') }}" class="text-blue-600 hover:underline">View Tax Reports</a>
            </div>
        </div>
        
        <div class="flex justify-end">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Save Tax Settings
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle tax settings visibility
        const enableTaxCheckbox = document.getElementById('enable-tax');
        const taxSettings = document.getElementById('tax-settings');
        
        enableTaxCheckbox.addEventListener('change', function() {
            taxSettings.classList.toggle('hidden', !this.checked);
        });
        
        // Toggle location-based tax settings visibility
        const enableLocationTaxCheckbox = document.getElementById('enable-location-tax');
        const locationTaxSettings = document.getElementById('location-tax-settings');
        
        enableLocationTaxCheckbox.addEventListener('change', function() {
            locationTaxSettings.classList.toggle('hidden', !this.checked);
        });
    });
</script>
@endpush
@endsection
