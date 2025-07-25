<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Purchase a Gift Card') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @include('gift-cards.partials.form')
                </div>
            </div>
        </div>
    </div>

    @include('gift-cards.partials.success-modal')
    @include('gift-cards.partials.stripe-js')
</x-app-layout>
