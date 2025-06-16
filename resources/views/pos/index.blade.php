@extends('layouts.app')

@push('styles')
<!-- Include the tax calculator styles -->
@vite(['resources/js/components/tax-calculator.js'])

<style>
    /* Custom scrollbar for cart */
    .cart-scroll {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #edf2f7;
    }
    .cart-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .cart-scroll::-webkit-scrollbar-track {
        background: #edf2f7;
        border-radius: 3px;
    }
    .cart-scroll::-webkit-scrollbar-thumb {
        background-color: #cbd5e0;
        border-radius: 3px;
    }
    /* Animation for cart items */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .cart-item {
        animation: fadeIn 0.2s ease-out forwards;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6" x-data="posApp()" x-init="init()" @keydown.escape="showPaymentModal = false">
    <div class="flex flex-col md:flex-row gap-6">
        <!-- Left Side: Products/Items -->
        <div class="w-full md:w-2/3">
            <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Products & Services</h2>
                    <div class="relative">
                        <input type="text"
                               x-model="searchQuery"
                               @input="filterProducts()"
                               placeholder="Search products..."
                               class="w-64 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Categories Tabs -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <button
                        @click="setActiveCategory('all')"
                        :class="{'bg-blue-600 text-white': activeCategory === 'all'}"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    >
                        All
                    </button>
                    <template x-for="category in categories" :key="category.id">
                        <button
                            @click="setActiveCategory(category.id)"
                            :class="{'bg-blue-600 text-white': activeCategory === category.id}"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                            x-text="category.name"
                        ></button>
                    </template>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div class="border rounded-lg overflow-hidden hover:shadow-md transition-shadow cursor-pointer"
                             @click="addToCart(product)">
                            <div class="p-4">
                                <h3 class="font-medium text-gray-900" x-text="product.name"></h3>
                                <p class="text-gray-600 text-sm mt-1" x-text="formatPrice(product.price)"></p>
                                <div class="mt-2 text-xs text-gray-500" x-show="product.type === 'product'" x-text="'In Stock: ' + product.quantity_in_stock"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Right Side: Cart & Checkout -->
        <div class="w-full md:w-1/3">
            <div class="bg-white rounded-lg shadow-md p-4 sticky top-4">
                <h2 class="text-xl font-semibold mb-4">Order Summary</h2>

                <!-- Customer Selection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                    <select x-model="currentCustomer" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value="">Walk-in Customer</option>
                        <template x-for="customer in customers" :key="customer.id">
                            <option :value="customer.id" x-text="customer.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Cart Items -->
                <div class="border-t border-b py-4 my-4 max-h-96 overflow-y-auto cart-scroll">
                    <template x-if="cart.length === 0">
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Your cart is empty</h3>
                            <p class="mt-1 text-sm text-gray-500">Start adding products to get started</p>
                        </div>
                    </template>
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="cart-item flex items-start py-3 px-2 hover:bg-gray-50 rounded-lg transition-colors">
                            <div class="flex-shrink-0 h-12 w-12 rounded-md overflow-hidden bg-gray-100 flex items-center justify-center">
                                <template x-if="item.image">
                                    <img :src="item.image" :alt="item.name" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!item.image">
                                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </template>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-900" x-text="item.name"></h4>
                                    <button @click="removeFromCart(index)" class="text-gray-400 hover:text-red-500 transition-colors">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="mt-1 text-sm text-gray-500" x-text="formatPrice(item.price) + ' each'"></p>
                                <div class="mt-2 flex items-center">
                                    <button @click="updateQuantity(index, item.quantity - 1)"
                                            class="text-gray-500 hover:text-blue-600 p-1 rounded-full hover:bg-gray-100">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                        </svg>
                                    </button>
                                    <input type="number"
                                           x-model="item.quantity"
                                           @change="updateQuantity(index, parseInt($event.target.value) || 1)"
                                           class="w-12 mx-2 text-center border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                           min="1">
                                    <button @click="updateQuantity(index, item.quantity + 1)"
                                            class="text-gray-500 hover:text-blue-600 p-1 rounded-full hover:bg-gray-100">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                    <span class="ml-auto font-medium text-gray-900" x-text="formatPrice(item.price * item.quantity)"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Order Summary -->
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Order Summary</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Subtotal (<span x-text="cart.reduce((sum, item) => sum + item.quantity, 0) + ' items'"></span>)</span>
                            <span x-text="formatPrice(cartSubtotal)" class="font-medium"></span>
                        </div>
                        <div class="flex justify-between text-sm" x-show="taxBreakdown.length > 0">
                            <span class="text-gray-500">Tax</span>
                            <span x-text="formatPrice(totalTax)"
                                  :title="getTaxBreakdownText()"
                                  class="cursor-help border-b border-dashed border-gray-500"
                                  x-tooltip.placement.top="getTaxBreakdownText()"></span>
                        </div>
                        <div class="flex justify-between text-sm" x-show="discount > 0">
                            <span class="text-green-600">Discount</span>
                            <span class="text-green-600 font-medium" x-text="'-' + formatPrice(discount)"></span>
                        </div>
                        <div class="border-t border-gray-200 pt-3 mt-2 flex justify-between">
                            <span class="text-base font-medium text-gray-900">Total</span>
                            <span class="text-base font-bold text-gray-900" x-text="formatPrice(cartTotal)"></span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-2">
                    <button @click="openPaymentModal()"
                            :disabled="cart.length === 0"
                            :class="{
                                'opacity-50 cursor-not-allowed': cart.length === 0,
                                'bg-blue-600 hover:bg-blue-700': cart.length > 0
                            }"
                            class="w-full flex items-center justify-center px-4 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 3v2m10-2v2M7 19v2m10-2v2M5 10l-.868 12.142A2 2 0 006.137 24h11.726a2 2 0 002.005-1.858L19 10H5zM10 10v6m4-6v6" />
                        </svg>
                        Checkout (Ctrl+Alt+P)
                    </button>

                    <button @click="clearCart()"
                            :disabled="cart.length === 0"
                            class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-md shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Clear Cart
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div x-show="showPaymentModal"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50"
         @click.self="showPaymentModal = false">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden" @click.stop>
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Complete Payment</h3>
            </div>

            <div class="px-6 py-4 space-y-4">
                <!-- Order Summary -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Order Summary</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Subtotal</span>
                            <span x-text="formatPrice(subtotal)" class="font-medium"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Tax</span>
                            <span x-text="formatPrice(tax)" class="font-medium"></span>
                        </div>
                        <div class="border-t border-gray-200 pt-2 mt-2 flex justify-between">
                            <span class="font-medium">Total</span>
                            <span class="font-bold" x-text="formatPrice(total)"></span>
                        </div>
                        <!-- Add this near the tax line in your order summary -->
<div x-show="isLoadingTaxRates" class="text-sm text-gray-500">
    Calculating taxes...
</div>
<div x-show="taxError" class="text-sm text-red-500" x-text="taxError"></div>
                    </div>
                </div>

                <!-- Email Receipt Option -->
                <div class="pt-2">
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="emailReceipt" x-model="emailReceipt" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="emailReceipt" class="ml-2 block text-sm text-gray-700">
                            Send email receipt
                        </label>
                    </div>
                    <div x-show="emailReceipt" class="mt-2" x-transition>
                        <label for="customerEmail" class="block text-sm font-medium text-gray-700 mb-1">
                            Email Address
                        </label>
                        <div class="mt-1">
                            <input type="email"
                                   id="customerEmail"
                                   x-model="customerEmail"
                                   :value="currentCustomerEmail"
                                   class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                   placeholder="customer@example.com">
                        </div>
                        <p class="mt-1 text-xs text-gray-500" id="email-description">
                            A receipt will be sent to this email address.
                        </p>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="pt-2">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Payment Method</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <button @click="processPayment('cash')"
                                class="flex-1 flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Cash
                        </button>
                        <button @click="processPayment('card')"
                                class="flex-1 flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            Card
                        </button>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                <button @click="showPaymentModal = false" type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
        // Show toast notification
        showToast(message, type = 'success') {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white ${
                type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            toast.textContent = message;

            // Add to DOM
            document.body.appendChild(toast);

            // Remove after delay
            setTimeout(() => {
                toast.remove();
            }, 3000);
        },
    };
}

function posApp() {
    return {
        // Data
        searchQuery: '',
        activeCategory: 'all',
        categories: [],
        products: [],
        filteredProducts: [],
        customers: [],
        currentCustomer: '',
        currentCustomerEmail: '',
        cart: [],

        taxRate: 0.1, // 10% tax rate
        // Tax-related properties
taxRates: [],
totalTax: 0,
taxBreakdown: [],
isLoadingTaxRates: false,
taxError: null,
        showPaymentModal: false,
        emailReceipt: false,
        customerEmail: '',
        isSendingEmail: false,
// Computed properties
get cartSubtotal() {
    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
},

get cartTotal() {
    return this.cartSubtotal + this.totalTax;
}
        // Computed properties
        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        get tax() {
            return this.subtotal * this.taxRate;
        },

        get total() {
            return this.subtotal + this.tax;
        },

        // Methods
        async init() {
            await this.fetchData();
            this.filterProducts();
            await this.loadTaxRates();
        },
// Format currency
formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2
    }).format(amount);
},

// Get tax breakdown as text for tooltip
getTaxBreakdownText() {
    if (!this.taxBreakdown || this.taxBreakdown.length === 0) return 'No tax applied';
    return this.taxBreakdown.map(tax =>
        `${tax.name} (${tax.rate}%): ${this.formatCurrency(tax.amount)}`
    ).join('\n');
},

// Load tax rates from the API
async loadTaxRates() {
    this.isLoadingTaxRates = true;
    this.taxError = null;

    try {
        const response = await fetch('/api/tax-rates');
        const data = await response.json();

        if (data.success) {
            this.taxRates = data.data;
            await this.calculateTaxes();
        } else {
            this.taxError = data.message || 'Failed to load tax rates';
            console.error('Failed to load tax rates:', this.taxError);
        }
    } catch (error) {
        this.taxError = 'Error loading tax rates';
        console.error('Error loading tax rates:', error);
    } finally {
        this.isLoadingTaxRates = false;
    }
},

// Calculate taxes for the cart
async calculateTaxes() {
    if (this.cart.length === 0) {
        this.totalTax = 0;
        this.taxBreakdown = [];
        return;
    }

    try {
        // Group items by tax rate
        const itemsByTax = {};
        let subtotal = 0;

        // Calculate subtotal and group items
        this.cart.forEach(item => {
            const itemSubtotal = item.price * item.quantity;
            subtotal += itemSubtotal;

            // For each item, determine its tax rate
            // In a real app, you'd look up the correct tax rate for each item
            const taxRate = this.taxRates[0] || { id: 'default', rate: 0, name: 'Tax' };
            const taxKey = `${taxRate.id}-${taxRate.rate}`;

            if (!itemsByTax[taxKey]) {
                itemsByTax[taxKey] = {
                    ...taxRate,
                    amount: 0,
                    taxableAmount: 0
                };
            }

            itemsByTax[taxKey].taxableAmount += itemSubtotal;
        });

        // Calculate tax for each group
        this.taxBreakdown = Object.values(itemsByTax).map(group => ({
            ...group,
            amount: group.taxableAmount * (group.rate / 100)
        }));

        // Calculate total tax
        this.totalTax = this.taxBreakdown.reduce((sum, tax) => sum + tax.amount, 0);

    } catch (error) {
        console.error('Error calculating taxes:', error);
        this.totalTax = 0;
        this.taxBreakdown = [];
    }
}
        async fetchData() {
            try {
                // Fetch products and categories
                const [productsRes, categoriesRes, customersRes] = await Promise.all([
                    fetch('/api/products').then(res => res.json()),
                    fetch('/api/categories').then(res => res.json()),
                    fetch('/api/clients').then(res => res.json())
                ]);

                this.products = productsRes.data || [];
                this.categories = categoriesRes.data || [];
                this.customers = customersRes.data || [];

                // Set current customer email if a customer is selected
                if (this.currentCustomer) {
                    const customer = this.customers.find(c => c.id == this.currentCustomer);
                    if (customer && customer.email) {
                        this.currentCustomerEmail = customer.email;
                        this.customerEmail = customer.email;
                    }
                }
            } catch (error) {
                console.error('Error fetching data:', error);
                this.showToast('Failed to load data. Please try again.', 'error');
            }
        },

        filterProducts() {
            this.filteredProducts = this.products.filter(product => {
                const matchesSearch = product.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                                   product.description?.toLowerCase().includes(this.searchQuery.toLowerCase());
                const matchesCategory = this.activeCategory === 'all' ||
                                       product.category_id == this.activeCategory;
                return matchesSearch && matchesCategory;
            });
        },

        setActiveCategory(categoryId) {
            this.activeCategory = categoryId;
            this.filterProducts();
        },

        openPaymentModal() {
            // If a customer is selected, pre-fill their email
            if (this.currentCustomer) {
                const customer = this.customers.find(c => c.id == this.currentCustomer);
                if (customer && customer.email) {
                    this.customerEmail = customer.email;
                    this.emailReceipt = true;
                }
            }
            this.showPaymentModal = true;
        },

        addToCart(product) {
            const existingItem = this.cart.find(item => item.id === product.id && item.type === product.type);
            this.calculateTaxes();
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                this.cart.push({
                    id: product.id,
                    type: product.type || 'product',
                    name: product.name,
                    price: product.selling_price || product.price,
                    quantity: 1
                });
            }
        },

        updateQuantity(index, newQuantity) {
            if (newQuantity < 1) return;
            this.cart[index].quantity = newQuantity;
            this.calculateTaxes();
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
            this.calculateTaxes();
        },

        async processPayment(paymentMethod) {
            if (this.cart.length === 0) {
                this.showToast('Your cart is empty', 'error');
                return;
            }

            // Ensure taxes are up to date before processing payment
            await this.calculateTaxes();

            // Validate email if email receipt is requested
            if (this.emailReceipt && !this.validateEmail(this.customerEmail)) {
                this.showToast('Please enter a valid email address', 'error');
                return;
            }

            // Show loading state
            this.isSendingEmail = this.emailReceipt;

            // Calculate order totals
            const subtotal = this.subtotal;
            const tax = this.tax;
            const total = this.total;

            try {
                const orderData = {
                    customer_id: this.currentCustomer || null,
                    items: this.cart.map(item => ({
                        id: item.id,
                        type: item.type,
                        quantity: item.quantity,
                        price: item.price,
                        name: item.name
                    })),
                    payment_method: paymentMethod,
                    amount_paid: total,
                    total_amount: total,
                    tax_amount: tax,
                    subtotal: subtotal,
                    send_email: this.emailReceipt,
                    customer_email: this.emailReceipt ? this.customerEmail : null
                };

                const response = await fetch('{{ route("pos.process-payment") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(orderData)
                });

                const result = await response.json();

                if (response.ok) {
                    // Show success message
                    let successMessage = 'Payment processed successfully!';
                    if (this.emailReceipt) {
                        successMessage += ' Receipt has been sent to ' + this.customerEmail;
                    }
                    this.showToast(successMessage, 'success');

                    // Open receipt in new tab for printing
                    if (result.receipt_url) {
                        const receiptWindow = window.open(result.receipt_url, '_blank');
                        // Focus the window (might be blocked by popup blockers)
                        if (receiptWindow) {
                            receiptWindow.focus();
                        }
                    }

                    // Reset the cart and form
                    this.cart = [];
                    this.currentCustomer = '';
                    this.searchQuery = '';
                    this.emailReceipt = false;
                    this.customerEmail = '';

                    // Close payment modal if open
                    this.showPaymentModal = false;
                } else {
                    throw new Error(result.message || 'Failed to process payment');
                }
            } catch (error) {
                console.error('Error processing payment:', error);
                this.showToast('Failed to process payment: ' + error.message, 'error');
            } finally {
                this.isSendingEmail = false;
            }
        },

        formatPrice(price) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(price);
        },

        validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(String(email).toLowerCase());
        }
    };
}
</script>
@endpush
@endsection
