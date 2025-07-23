@extends('layouts.print')

@section('content')
<div class="receipt-container max-w-md mx-auto p-6 bg-white relative">
    <!-- Action Buttons (Visible on screen only) -->
    <div class="no-print flex justify-end space-x-4 mb-6">
        <button onclick="window.print()" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print Receipt
        </button>
        <a href="{{ route('pos.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to POS
        </a>
    </div>
    <!-- Receipt Header -->
    <div class="text-center mb-6">
        @if($businessLogo = setting('business.logo'))
            <img src="{{ asset('storage/' . $businessLogo) }}" alt="{{ config('app.name') }}" class="h-16 mx-auto mb-2">
        @endif
        <h1 class="text-2xl font-bold">{{ config('app.name') }}</h1>
        <p class="text-sm text-gray-600">{{ setting('business.address') }}</p>
        <p class="text-sm text-gray-600">Phone: {{ setting('business.phone') }}</p>
        <p class="text-sm text-gray-600">{{ setting('business.email') }}</p>
    </div>

    <!-- Order Info -->
    <div class="border-b border-gray-200 pb-4 mb-4">
        <div class="flex justify-between">
            <span class="font-medium">Receipt #</span>
            <span>{{ $order->order_number }}</span>
        </div>
        <div class="flex justify-between">
            <span class="font-medium">Date</span>
            <span>{{ $order->created_at->format('M d, Y h:i A') }}</span>
        </div>
        @if($order->client)
        <div class="mt-2">
            <p class="font-medium">Customer:</p>
            <p>{{ $order->client->name }}</p>
            @if($order->client->phone)
                <p>{{ $order->client->phone }}</p>
            @endif
            @if($order->client->email)
                <p>{{ $order->client->email }}</p>
            @endif
        </div>
        @endif
    </div>

    <!-- Order Items -->
    <div class="mb-4">
        <div class="grid grid-cols-12 gap-2 mb-2 font-medium border-b pb-1">
            <div class="col-span-6">Item</div>
            <div class="col-span-2 text-center">Qty</div>
            <div class="col-span-2 text-right">Price</div>
            <div class="col-span-2 text-right">Total</div>
        </div>
        @foreach($order->items as $item)
        <div class="grid grid-cols-12 gap-2 py-1 border-b border-gray-100">
            <div class="col-span-6">
                {{ $item->name }}
                @if($item->type === 'service' && $item->pivot->service_employee_id)
                    <div class="text-xs text-gray-500">
                        Technician: {{ $item->serviceEmployee->name }}
                    </div>
                @endif
            </div>
            <div class="col-span-2 text-center">{{ $item->pivot->quantity }}</div>
            <div class="col-span-2 text-right">{{ number_format($item->pivot->price, 2) }}</div>
            <div class="col-span-2 text-right font-medium">{{ number_format($item->pivot->price * $item->pivot->quantity, 2) }}</div>
        </div>
        @endforeach
    </div>

    <!-- Order Totals -->
    <div class="border-t-2 border-b-2 border-gray-200 py-3 my-4">
        <div class="flex justify-between mb-1">
            <span>Subtotal:</span>
            <span>${{ number_format($order->subtotal, 2) }}</span>
        </div>
        @if($order->discount > 0)
        <div class="flex justify-between mb-1">
            <span>Discount:</span>
            <span class="text-red-600">-${{ number_format($order->discount, 2) }}</span>
        </div>
        @endif
        <div class="flex justify-between mb-1">
            <span>Tax ({{ $order->tax_rate }}%):</span>
            <span>${{ number_format($order->tax, 2) }}</span>
        </div>
        <div class="flex justify-between text-lg font-bold mt-2">
            <span>Total:</span>
            <span>${{ number_format($order->total, 2) }}</span>
        </div>
    </div>

    <!-- Payment Info -->
    <div class="mb-6">
        <div class="flex justify-between">
            <span class="font-medium">Payment Method:</span>
            <span class="capitalize">{{ $order->payment_method }}</span>
        </div>
        @if($order->payment_reference)
        <div class="flex justify-between">
            <span class="font-medium">Reference:</span>
            <span>{{ $order->payment_reference }}</span>
        </div>
        @endif
        @if($order->amount_paid > 0)
        <div class="flex justify-between">
            <span class="font-medium">Amount Paid:</span>
            <span>${{ number_format($order->amount_paid, 2) }}</span>
        </div>
        @endif
        @if($order->change > 0)
        <div class="flex justify-between">
            <span class="font-medium">Change:</span>
            <span>${{ number_format($order->change, 2) }}</span>
        </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="text-center text-xs text-gray-500 mt-8">
        <p>Thank you for your business!</p>
        <p class="mt-2">{{ setting('business.return_policy', 'All sales are final. No returns or exchanges.') }}</p>
        <p class="mt-1">Receipt #{{ $order->order_number }}</p>
        <p class="mt-4">{{ now()->format('m/d/Y h:i A') }}</p>
    </div>
</div>

@push('scripts')
<script>
    // Auto-print the receipt when the page loads
    window.addEventListener('load', function() {
        // Check if we're in an iframe (preview) or not
        if(window.self === window.top) {
            window.print();
            // Close the window after printing if it was opened in a new tab
            setTimeout(function() {
                window.close();
            }, 1000);
        }
    });

    // Handle print dialog close
    window.onafterprint = function() {
        // Only close if we're not in an iframe
        if(window.self === window.top) {
            window.close();
        }
    };
</script>
@endpush

@push('styles')
<style>
    @media print {
        @page {
            size: 80mm auto;
            margin: 0;
            padding: 10px;
        }
        body {
            width: 100% !important;
            margin: 0 !important;
            padding: 10px !important;
            font-size: 12px !important;
            line-height: 1.4 !important;
        }
        .receipt-container {
            width: 100% !important;
            max-width: 100% !important;
            padding: 10px !important;
            margin: 0 !important;
        }
        .no-print {
            display: none !important;
        }
        .print-only {
            display: block !important;
        }
    }
    @media screen {
        .receipt-container {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .print-only {
            display: none;
        }
    }
    .print-only {
        display: none;
    }
</style>
@endpush
@endsection
