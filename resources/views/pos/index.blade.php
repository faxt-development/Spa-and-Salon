@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="posApp()" x-init="init()">
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
                <div class="border-t border-b py-4 my-4 max-h-96 overflow-y-auto">
                    <template x-if="cart.length === 0">
                        <p class="text-gray-500 text-center py-4">Your cart is empty</p>
                    </template>
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="flex justify-between items-center py-2 border-b">
                            <div>
                                <h4 class="font-medium" x-text="item.name"></h4>
                                <div class="flex items-center mt-1">
                                    <button @click="updateQuantity(index, item.quantity - 1)" class="text-gray-500 hover:text-gray-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                        </svg>
                                    </button>
                                    <input type="number" x-model="item.quantity" @change="updateQuantity(index, parseInt($event.target.value) || 1)" 
                                           class="w-12 text-center border-0 focus:ring-0" min="1">
                                    <button @click="updateQuantity(index, item.quantity + 1)" class="text-gray-500 hover:text-gray-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="text-right">
                                <div x-text="formatPrice(item.price * item.quantity)" class="font-medium"></div>
                                <button @click="removeFromCart(index)" class="text-red-500 text-sm hover:text-red-700">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Order Totals -->
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span x-text="formatPrice(subtotal)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tax:</span>
                        <span x-text="formatPrice(tax)"></span>
                    </div>
                    <div class="flex justify-between font-bold text-lg border-t pt-2 mt-2">
                        <span>Total:</span>
                        <span x-text="formatPrice(total)"></span>
                    </div>
                </div>

                <!-- Payment Buttons -->
                <div class="space-y-2">
                    <button @click="processPayment('cash')" 
                            class="w-full bg-green-600 text-white py-3 rounded-lg font-medium hover:bg-green-700 transition-colors">
                        Cash Payment
                    </button>
                    <button @click="processPayment('card')" 
                            class="w-full bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                        Card Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
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
        cart: [],
        taxRate: 0.1, // 10% tax rate

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
        },

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
            } catch (error) {
                console.error('Error fetching data:', error);
                alert('Failed to load data. Please try again.');
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

        addToCart(product) {
            const existingItem = this.cart.find(item => item.id === product.id && item.type === product.type);
            
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
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        async processPayment(paymentMethod) {
            if (this.cart.length === 0) {
                alert('Your cart is empty');
                return;
            }

            try {
                const orderData = {
                    client_id: this.currentCustomer || null,
                    items: this.cart.map(item => ({
                        type: item.type,
                        id: item.id,
                        quantity: item.quantity,
                        unit_price: item.price
                    })),
                    payment_method: paymentMethod
                };

                const response = await fetch('/api/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(orderData)
                });

                const result = await response.json();

                if (response.ok) {
                    alert('Order placed successfully!');
                    this.cart = [];
                    this.currentCustomer = '';
                } else {
                    throw new Error(result.message || 'Failed to place order');
                }
            } catch (error) {
                console.error('Error processing payment:', error);
                alert('Failed to process payment: ' + error.message);
            }
        },

        formatPrice(price) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(price);
        }
    };
}
</script>
@endpush
@endsection
