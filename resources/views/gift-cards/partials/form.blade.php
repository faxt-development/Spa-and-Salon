<div x-data="giftCardForm()" @submit.prevent="submitForm">
    <form id="gift-card-form" class="space-y-6">
        <!-- Amount Selection -->
        <div>
            <label for="amount" class="block text-sm font-medium text-gray-700">
                {{ __('Amount') }} ({{ strtoupper(config('services.stripe.currency', 'usd')) }})
            </label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">$</span>
                </div>
                <input
                    type="number"
                    id="amount"
                    x-model="form.amount"
                    min="5"
                    max="1000"
                    step="0.01"
                    class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md"
                    placeholder="0.00"
                    required
                >
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">{{ strtoupper(config('services.stripe.currency', 'usd')) }}</span>
                </div>
            </div>
            <p class="mt-1 text-sm text-gray-500">
                {{ __('Amount must be between') }} $5.00 and $1,000.00
            </p>
            <span x-show="errors.amount" x-text="errors.amount" class="text-red-500 text-xs"></span>
        </div>

        <!-- Recipient Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="recipient_name" class="block text-sm font-medium text-gray-700">
                    {{ __("Recipient's Name") }}
                </label>
                <input
                    type="text"
                    id="recipient_name"
                    x-model="form.recipient_name"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    required
                >
                <span x-show="errors.recipient_name" x-text="errors.recipient_name" class="text-red-500 text-xs"></span>
            </div>
            <div>
                <label for="recipient_email" class="block text-sm font-medium text-gray-700">
                    {{ __("Recipient's Email") }}
                </label>
                <input
                    type="email"
                    id="recipient_email"
                    x-model="form.recipient_email"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    required
                >
                <span x-show="errors.recipient_email" x-text="errors.recipient_email" class="text-red-500 text-xs"></span>
            </div>
        </div>

        <!-- Sender Information -->
        <div>
            <label for="sender_name" class="block text-sm font-medium text-gray-700">
                {{ __('Your Name') }}
            </label>
            <input
                type="text"
                id="sender_name"
                x-model="form.sender_name"
                :disabled="isAuthenticated"
                :class="{
                    'bg-gray-50': isAuthenticated,
                    'border-gray-300': isAuthenticated,
                    'focus:ring-indigo-500 focus:border-indigo-500': !isAuthenticated
                }"
                class="mt-1 block w-full rounded-md shadow-sm py-2 px-3 sm:text-sm"
                required
            >
            <span x-show="errors.sender_name" x-text="errors.sender_name" class="text-red-500 text-xs"></span>
            <p x-show="isAuthenticated" class="mt-1 text-xs text-gray-500">
                {{ __('Logged in as:') }} {{ $userEmail }}
            </p>
        </div>

        <!-- Sender Email -->
        <div x-show="!isAuthenticated">
            <label for="sender_email" class="block text-sm font-medium text-gray-700">
                {{ __('Your Email') }}
            </label>
            <input
                type="email"
                id="sender_email"
                x-model="form.sender_email"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :required="!isAuthenticated"
            >
            <span x-show="errors.sender_email" x-text="errors.sender_email" class="text-red-500 text-xs"></span>
        </div>

        <!-- Message -->
        <div>
            <label for="message" class="block text-sm font-medium text-gray-700">
                {{ __('Personal Message') }} ({{ __('Optional') }})
            </label>
            <textarea
                id="message"
                x-model="form.message"
                rows="3"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                maxlength="1000"
            ></textarea>
            <span x-show="errors.message" x-text="errors.message" class="text-red-500 text-xs"></span>
        </div>

        <!-- Expiration Date -->
        <div>
            <label for="expires_at" class="block text-sm font-medium text-gray-700">
                {{ __('Expiration Date') }} ({{ __('Optional') }})
            </label>
            <input
                type="date"
                id="expires_at"
                x-model="form.expires_at"
                :min="minExpiryDate"
                :max="maxExpiryDate"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
            <p class="mt-1 text-sm text-gray-500">
                {{ __('If not specified, gift card will expire in 1 year') }}
            </p>
            <span x-show="errors.expires_at" x-text="errors.expires_at" class="text-red-500 text-xs"></span>
        </div>

        <!-- Card Element -->
        <div class="border-t border-gray-200 pt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ __('Payment Information') }}
            </label>
            <div id="card-element" class="border border-gray-300 rounded-md p-3">
                <!-- A Stripe Element will be inserted here. -->
            </div>
            <!-- Used to display form errors. -->
            <div id="card-errors" role="alert" class="text-red-500 text-sm mt-2" x-text="cardError"></div>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button
                type="submit"
                :disabled="processing"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <template x-if="processing">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
                <span x-text="processing ? 'Processing...' : 'Pay $' + (form.amount ? parseFloat(form.amount).toFixed(2) : '0.00')"></span>
            </button>
        </div>
    </form>
</div>
