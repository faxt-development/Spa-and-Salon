@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header with Back and Action Buttons -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center">
                <a href="{{ route('inventory.products.index') }}" 
                   class="text-gray-500 hover:text-gray-700 mr-4">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-800">{{ $product->name }}</h1>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('inventory.products.edit', $product) }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Product
                </a>
                <button type="button" 
                        @click="adjustInventoryOpen = true"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Adjust Inventory
                </button>
            </div>
        </div>

        <!-- Product Details -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Product Information
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Detailed information about the product.
                </p>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">SKU</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $product->sku }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Barcode</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $product->barcode ?? 'Not set' }}
                            @if($product->barcode)
                                <div class="mt-2">
                                    {!! DNS1D::getBarcodeHTML($product->barcode, 'EAN13') !!}
                                </div>
                            @endif
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $product->category ? $product->category->name : 'Uncategorized' }}
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Brand</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $product->brand ?? 'Not specified' }}
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $product->description ?? 'No description provided.' }}
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                            @if($product->is_active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Inactive
                                </span>
                            @endif
                            
                            @if($product->is_taxable)
                                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Taxable ({{ $product->tax_rate }}%)
                                </span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Pricing and Inventory -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Pricing Card -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Pricing
                    </h3>
                </div>
                <div class="border-t border-gray-200">
                    <dl>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Cost Price</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                ${{ number_format($product->cost_price, 2) }}
                            </dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Selling Price</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                ${{ number_format($product->selling_price, 2) }}
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Profit Margin</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                @php
                                    $profit = $product->selling_price - $product->cost_price;
                                    $margin = $product->cost_price > 0 ? ($profit / $product->cost_price) * 100 : 100;
                                @endphp
                                ${{ number_format($profit, 2) }} ({{ number_format($margin, 2) }}%)
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Inventory Card -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Inventory
                    </h3>
                </div>
                <div class="border-t border-gray-200">
                    <dl>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Current Stock</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <div class="flex items-center">
                                    <span class="mr-2">{{ $product->quantity_in_stock }}</span>
                                    @if($product->quantity_in_stock <= 0)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Out of Stock
                                        </span>
                                    @elseif($product->quantity_in_stock <= $product->minimum_stock_level)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Low Stock
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            In Stock
                                        </span>
                                    @endif
                                </div>
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Minimum Stock Level</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $product->minimum_stock_level }}
                            </dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Reorder Quantity</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $product->reorder_quantity }}
                            </dd>
                        </div>
                        @if($product->supplier)
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Supplier</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $product->supplier->name }}
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <!-- Inventory History -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Inventory History
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Recent inventory adjustments for this product.
                    </p>
                </div>
                <button type="button" 
                        @click="adjustInventoryOpen = true"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Adjust Inventory
                </button>
            </div>
            <div class="border-t border-gray-200">
                @if($product->inventory->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Adjustment
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        New Quantity
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Reason
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($product->inventory->sortByDesc('created_at') as $inventory)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $inventory->created_at->format('M d, Y h:i A') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ ucfirst($inventory->type) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $inventory->adjustment >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $inventory->adjustment >= 0 ? '+' : '' }}{{ $inventory->adjustment }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $inventory->quantity_after }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ $inventory->reason }}
                                            @if($inventory->notes)
                                                <p class="text-xs text-gray-400 mt-1">{{ $inventory->notes }}</p>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $inventory->user->name ?? 'System' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-4 text-center text-sm text-gray-500">
                        No inventory history available for this product.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Adjust Inventory Modal -->
<div x-show="adjustInventoryOpen" 
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed z-10 inset-0 overflow-y-auto" 
     aria-labelledby="modal-title" 
     x-ref="dialog" 
     aria-modal="true"
     x-cloak>
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="adjustInventoryOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             x-description="Background overlay, show/hide based on modal state." 
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
             @click="adjustInventoryOpen = false"
             aria-hidden="true">
        </div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="adjustInventoryOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Adjust Inventory
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Update the inventory count for {{ $product->name }}.
                        </p>
                    </div>
                </div>
            </div>
            <form action="{{ route('inventory.products.adjust', $product) }}" method="POST" class="mt-5 sm:mt-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="adjustment" class="block text-sm font-medium text-gray-700">
                            Adjustment
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">
                                    {{ $product->quantity_in_stock }} + 
                                </span>
                            </div>
                            <input type="number" 
                                   name="adjustment" 
                                   id="adjustment" 
                                   x-model="adjustment"
                                   @input="updateNewQuantity($event.target.value)"
                                   class="pl-20 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                   placeholder="0">
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            Use a positive number to add stock, negative to remove.
                        </p>
                    </div>

                    <div>
                        <label for="new_quantity" class="block text-sm font-medium text-gray-700">
                            New Quantity
                        </label>
                        <div class="mt-1">
                            <input type="number" 
                                   id="new_quantity" 
                                   x-model="newQuantity"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                   disabled>
                        </div>
                    </div>

                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700">
                            Reason for Adjustment *
                        </label>
                        <div class="mt-1">
                            <select id="reason" 
                                    name="reason" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                    required>
                                <option value="">Select a reason</option>
                                <option value="Stock Received">Stock Received</option>
                                <option value="Inventory Correction">Inventory Correction</option>
                                <option value="Damaged">Damaged</option>
                                <option value="Lost">Lost</option>
                                <option value="Theft">Theft</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">
                            Notes
                        </label>
                        <div class="mt-1">
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:col-start-2 sm:text-sm">
                        Update Inventory
                    </button>
                    <button type="button" 
                            @click="adjustInventoryOpen = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('inventoryAdjustment', () => ({
            adjustInventoryOpen: false,
            adjustment: 0,
            newQuantity: {{ $product->quantity_in_stock }},
            
            init() {
                // Initialize any required data
            },
            
            updateNewQuantity(value) {
                const currentStock = {{ $product->quantity_in_stock }};
                const adjustment = parseInt(value) || 0;
                this.newQuantity = currentStock + adjustment;
            }
        }));
    });
</script>
@endpush

<style>
    [x-cloak] { display: none !important; }
</style>

@endsection
