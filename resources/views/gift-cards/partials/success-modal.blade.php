<div x-show="showSuccess" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" x-cloak>
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="mt-3 text-lg font-medium text-gray-900">
                {{ __('Gift Card Purchased Successfully!') }}
            </h3>
            <div class="mt-2">
                <p class="text-sm text-gray-500">
                    {{ __('A gift card has been sent to') }} <span x-text="form.recipient_email" class="font-medium"></span>.
                </p>
                <p class="mt-2 text-sm text-gray-500">
                    {{ __('Gift card code:') }} <span x-text="giftCardCode" class="font-mono font-bold"></span>
                </p>
            </div>
            <div class="mt-4">
                <button
                    type="button"
                    @click="showSuccess = false"
                    class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm"
                >
                    {{ __('Close') }}
                </button>
                <button
                    type="button"
                    @click="printGiftCard"
                    class="ml-3 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm"
                >
                    {{ __('Print Gift Card') }}
                </button>
            </div>
        </div>
    </div>
</div>
