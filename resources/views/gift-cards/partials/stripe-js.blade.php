@push('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('giftCardForm', (initialData = {}) => ({
                stripe: null,
                cardElement: null,
                form: {
                    amount: '25.00',
                    recipient_name: '',
                    recipient_email: '',
                    sender_name: initialData.sender_name || '',
                    sender_email: initialData.sender_email || '',
                    message: '',
                    expires_at: ''
                },
                isAuthenticated: initialData.is_authenticated || false,
                errors: {},
                processing: false,
                showSuccess: false,
                giftCardCode: '',
                minExpiryDate: new Date().toISOString().split('T')[0],
                maxExpiryDate: new Date(new Date().setFullYear(new Date().getFullYear() + 2)).toISOString().split('T')[0],

                init() {
                    // Initialize Stripe
                    this.stripe = Stripe('{{ config('services.stripe.key') }}');
                    const elements = this.stripe.elements();
                    
                    // Create card Element
                    this.cardElement = elements.create('card', {
                        style: {
                            base: {
                                fontSize: '16px',
                                color: '#32325d',
                                '::placeholder': {
                                    color: '#aab7c4'
                                },
                            },
                            invalid: {
                                color: '#fa755a',
                                iconColor: '#fa755a'
                            }
                        }
                    });

                    // Mount the card Element
                    this.cardElement.mount('#card-element');

                    // Handle real-time validation errors from the card Element
                    this.cardElement.on('change', (event) => {
                        const displayError = document.getElementById('card-errors');
                        if (event.error) {
                            this.cardError = event.error.message;
                        } else {
                            this.cardError = '';
                        }
                    });
                },

                async submitForm() {
                    this.processing = true;
                    this.errors = {};
                    this.cardError = '';

                    // Client-side validation
                    if (this.form.amount < 5 || this.form.amount > 1000) {
                        this.errors.amount = 'Amount must be between $5 and $1,000';
                        this.processing = false;
                        return;
                    }

                    try {
                        // Prepare form data
                        const formData = { ...this.form };
                        
                        // Remove sender_email if user is authenticated (it's not needed)
                        if (this.isAuthenticated) {
                            delete formData.sender_email;
                        }
                        
                        const response = await fetch('{{ route('api.gift-cards.create-payment-intent') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify(formData)
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Something went wrong');
                        }

                        // Confirm card payment
                        const { paymentIntent, error } = await this.stripe.confirmCardPayment(
                            data.client_secret,
                            {
                                payment_method: {
                                    card: this.cardElement,
                                    billing_details: {
                                        name: this.form.sender_name,
                                        email: this.form.recipient_email,
                                    },
                                },
                                receipt_email: this.form.recipient_email,
                            }
                        );

                        if (error) {
                            throw new Error(error.message);
                        }

                        if (paymentIntent.status === 'succeeded') {
                            // Show success message
                            this.giftCardCode = data.gift_card_code || 'N/A';
                            this.showSuccess = true;
                            
                            // Reset form
                            this.form = {
                                amount: '25.00',
                                recipient_name: '',
                                recipient_email: '',
                                sender_name: '',
                                message: '',
                                expires_at: ''
                            };
                            this.cardElement.clear();
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.cardError = error.message || 'An error occurred while processing your payment.';
                    } finally {
                        this.processing = false;
                    }
                },

                printGiftCard() {
                    // This would open a print-friendly version of the gift card
                    window.print();
                },

                formatAmount(amount) {
                    return parseFloat(amount || 0).toFixed(2);
                }
            }));
        });
    </script>
@endpush
