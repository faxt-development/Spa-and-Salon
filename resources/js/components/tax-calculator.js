/**
 * Tax Calculator Component
 * 
 * This component provides real-time tax calculation for order items.
 * It should be used with a form that contains order items with the following data attributes:
 * - data-tax-item: Marks an item row
 * - data-item-type: 'product' or 'service'
 * - data-item-id: The ID of the product or service
 * - data-quantity: The quantity input field
 * - data-unit-price: The unit price input field
 * - data-discount: The discount input field (optional)
 * - data-subtotal: Where to display the subtotal (quantity * unit price)
 * - data-tax: Where to display the tax amount
 * - data-total: Where to display the total (subtotal - discount + tax)
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('taxCalculator', (options = {}) => ({
        // Default options
        taxRates: [],
        isLoading: false,
        error: null,
        
        // Initialize the component
        init() {
            this.loadTaxRates();
            this.setupEventListeners();
        },
        
        // Load available tax rates from the API
        async loadTaxRates() {
            try {
                this.isLoading = true;
                const response = await fetch('/api/tax-rates');
                const data = await response.json();
                
                if (data.success) {
                    this.taxRates = data.data;
                } else {
                    this.error = 'Failed to load tax rates';
                    console.error('Failed to load tax rates:', data.message);
                }
            } catch (error) {
                this.error = 'An error occurred while loading tax rates';
                console.error('Error loading tax rates:', error);
            } finally {
                this.isLoading = false;
            }
        },
        
        // Set up event listeners for all tax-relevant form fields
        setupEventListeners() {
            // Listen for changes on any input that affects tax calculation
            this.$el.querySelectorAll('[data-tax-item]').forEach(item => {
                const inputs = item.querySelectorAll('[data-quantity], [data-unit-price], [data-discount]');
                inputs.forEach(input => {
                    input.addEventListener('change', () => this.calculateItemTax(item));
                    input.addEventListener('input', () => this.calculateItemTax(item));
                });
            });
        },
        
        // Calculate tax for a single item
        async calculateItemTax(itemElement) {
            try {
                const itemType = itemElement.dataset.itemType;
                const itemId = itemElement.dataset.itemId;
                const quantity = parseFloat(itemElement.querySelector('[data-quantity]')?.value) || 0;
                const unitPrice = parseFloat(itemElement.querySelector('[data-unit-price]')?.value) || 0;
                const discount = parseFloat(itemElement.querySelector('[data-discount]')?.value) || 0;
                
                // Skip if required fields are missing or invalid
                if (!itemType || !itemId || quantity <= 0 || unitPrice <= 0) {
                    this.updateItemTotals(itemElement, 0, 0, 0);
                    return;
                }
                
                // Calculate subtotal
                const subtotal = quantity * unitPrice;
                
                // Call the API to calculate tax
                const response = await fetch('/api/tax/calculate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        itemable_type: itemType,
                        itemable_id: itemId,
                        quantity: quantity,
                        unit_price: unitPrice,
                        discount: discount
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.updateItemTotals(
                        itemElement,
                        data.data.subtotal,
                        data.data.tax_amount,
                        data.data.total,
                        data.data.tax_rates
                    );
                } else {
                    console.error('Tax calculation failed:', data.message);
                    this.updateItemTotals(itemElement, subtotal, 0, subtotal - discount);
                }
            } catch (error) {
                console.error('Error calculating tax:', error);
                this.error = 'An error occurred while calculating tax';
            }
        },
        
        // Update the UI with calculated values
        updateItemTotals(itemElement, subtotal, tax, total, taxRates = []) {
            // Update subtotal display
            const subtotalElement = itemElement.querySelector('[data-subtotal]');
            if (subtotalElement) {
                subtotalElement.textContent = this.formatCurrency(subtotal);
            }
            
            // Update tax display
            const taxElement = itemElement.querySelector('[data-tax]');
            if (taxElement) {
                taxElement.textContent = this.formatCurrency(tax);
                
                // Add tax breakdown tooltip if tax rates are provided
                if (taxRates.length > 0) {
                    const tooltip = taxRates.map(rate => 
                        `${rate.name} (${rate.rate}%): ${this.formatCurrency(rate.amount)}`
                    ).join('\n');
                    
                    taxElement.title = tooltip;
                    taxElement.classList.add('cursor-help', 'border-b', 'border-dashed', 'border-gray-500');
                }
            }
            
            // Update total display
            const totalElement = itemElement.querySelector('[data-total]');
            if (totalElement) {
                totalElement.textContent = this.formatCurrency(total);
            }
            
            // Dispatch an event that the totals were updated
            itemElement.dispatchEvent(new CustomEvent('tax-updated', {
                detail: { subtotal, tax, total, taxRates },
                bubbles: true
            }));
        },
        
        // Format a number as currency
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount);
        },
        
        // Recalculate all items in the form
        recalculateAll() {
            this.$el.querySelectorAll('[data-tax-item]').forEach(item => {
                this.calculateItemTax(item);
            });
        }
    }));
});
