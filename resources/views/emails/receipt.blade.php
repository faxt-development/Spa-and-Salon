@component('mail::layout')
{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

{{-- Email Body --}}
@php
    $businessName = config('app.name');
    $businessAddress = setting('business.address', 'Your Business Address');
    $businessPhone = setting('business.phone', 'Your Phone Number');
    $businessEmail = setting('business.email', 'your@email.com');
    $order = $order ?? null;
@endphp

<!-- Email Container -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; color: #333333; line-height: 1.6;">
    <!-- Header -->
    <tr>
        <td style="padding: 20px 0; text-align: center; border-bottom: 1px solid #e5e5e5;">
            @if($logo = setting('business.logo'))
                <img src="{{ asset('storage/' . $logo) }}" alt="{{ $businessName }}" style="max-height: 80px; width: auto;">
            @else
                <h1 style="margin: 0; font-size: 24px; color: #333333;">{{ $businessName }}</h1>
            @endif
            <p style="margin: 10px 0 0; font-size: 14px; color: #666666;">
                {{ $businessAddress }}<br>
                Phone: {{ $businessPhone }}<br>
                Email: {{ $businessEmail }}
            </p>
        </td>
    </tr>

    <!-- Order Info -->
    <tr>
        <td style="padding: 20px 0;">
            <h2 style="margin: 0 0 15px; font-size: 20px; color: #333333;">Order Receipt</h2>
            <p style="margin: 0 0 10px; font-size: 14px;">
                <strong>Receipt #:</strong> {{ $order->order_number }}<br>
                <strong>Date:</strong> {{ $order->created_at->format('M d, Y h:i A') }}<br>
                @if($order->client)
                    <strong>Customer:</strong> {{ $order->client->name }}<br>
                    @if($order->client->email)
                        <strong>Email:</strong> {{ $order->client->email }}<br>
                    @endif
                    @if($order->client->phone)
                        <strong>Phone:</strong> {{ $order->client->phone }}
                    @endif
                @endif
            </p>
        </td>
    </tr>

    <!-- Order Items -->
    <tr>
        <td style="padding: 0 0 20px;">
            <table width="100%" cellpadding="10" cellspacing="0" border="0" style="border-collapse: collapse; width: 100%;">
                <thead>
                    <tr style="background-color: #f5f5f5;">
                        <th align="left" style="padding: 12px; text-align: left; border-bottom: 2px solid #e5e5e5;">Item</th>
                        <th align="center" style="padding: 12px; text-align: center; border-bottom: 2px solid #e5e5e5;">Qty</th>
                        <th align="right" style="padding: 12px; text-align: right; border-bottom: 2px solid #e5e5e5;">Price</th>
                        <th align="right" style="padding: 12px; text-align: right; border-bottom: 2px solid #e5e5e5;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr style="border-bottom: 1px solid #e5e5e5;">
                        <td style="padding: 12px; text-align: left; vertical-align: top;">
                            {{ $item->name }}
                            @if($item->type === 'service' && $item->pivot->service_employee_id)
                                <div style="font-size: 12px; color: #666666;">
                                    Technician: {{ $item->serviceEmployee->name ?? 'N/A' }}
                                </div>
                            @endif
                        </td>
                        <td style="padding: 12px; text-align: center; vertical-align: top;">
                            {{ $item->pivot->quantity }}
                        </td>
                        <td style="padding: 12px; text-align: right; vertical-align: top;">
                            {{ number_format($item->pivot->price, 2) }}
                        </td>
                        <td style="padding: 12px; text-align: right; vertical-align: top;">
                            {{ number_format($item->pivot->price * $item->pivot->quantity, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </td>
    </tr>

    <!-- Order Totals -->
    <tr>
        <td style="padding: 0 0 30px;">
            <table width="100%" cellpadding="10" cellspacing="0" border="0">
                <tr>
                    <td align="right" style="padding: 5px 10px; text-align: right;">
                        <strong>Subtotal:</strong>
                    </td>
                    <td align="right" style="padding: 5px 10px; text-align: right; width: 100px;">
                        ${{ number_format($order->subtotal, 2) }}
                    </td>
                </tr>
                @if($order->discount > 0)
                <tr>
                    <td align="right" style="padding: 5px 10px; text-align: right;">
                        <strong>Discount:</strong>
                    </td>
                    <td align="right" style="padding: 5px 10px; text-align: right; color: #dc3545;">
                        -${{ number_format($order->discount, 2) }}
                    </td>
                </tr>
                @endif
                <tr>
                    <td align="right" style="padding: 5px 10px; text-align: right;">
                        <strong>Tax ({{ $order->tax_rate ?? 0 }}%):</strong>
                    </td>
                    <td align="right" style="padding: 5px 10px; text-align: right;">
                        ${{ number_format($order->tax, 2) }}
                    </td>
                </tr>
                <tr>
                    <td align="right" style="padding: 10px; text-align: right; border-top: 1px solid #e5e5e5; font-size: 16px; font-weight: bold;">
                        Total:
                    </td>
                    <td align="right" style="padding: 10px; text-align: right; border-top: 1px solid #e5e5e5; font-size: 16px; font-weight: bold;">
                        ${{ number_format($order->total, 2) }}
                    </td>
                </tr>
                @if($order->amount_paid > 0)
                <tr>
                    <td align="right" style="padding: 5px 10px; text-align: right;">
                        <strong>Amount Paid:</strong>
                    </td>
                    <td align="right" style="padding: 5px 10px; text-align: right;">
                        ${{ number_format($order->amount_paid, 2) }}
                    </td>
                </tr>
                @endif
                @if($order->change > 0)
                <tr>
                    <td align="right" style="padding: 5px 10px; text-align: right;">
                        <strong>Change:</strong>
                    </td>
                    <td align="right" style="padding: 5px 10px; text-align: right;">
                        ${{ number_format($order->change, 2) }}
                    </td>
                </tr>
                @endif
            </table>
        </td>
    </tr>

    <!-- Payment Info -->
    <tr>
        <td style="padding: 20px 0; border-top: 1px solid #e5e5e5; border-bottom: 1px solid #e5e5e5;">
            <p style="margin: 0 0 10px; font-size: 14px;">
                <strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}<br>
                @if($order->payment_reference)
                    <strong>Reference:</strong> {{ $order->payment_reference }}<br>
                @endif
                <strong>Status:</strong> <span style="color: #28a745; font-weight: bold;">Paid</span>
            </p>
        </td>
    </tr>

    <!-- Footer -->
    <tr>
        <td style="padding: 20px 0 0; text-align: center; font-size: 13px; color: #666666;">
            <p style="margin: 0 0 10px;">
                Thank you for your business!<br>
                {{ setting('business.return_policy', 'All sales are final. No returns or exchanges.') }}
            </p>
            <p style="margin: 0 0 10px; font-size: 12px; color: #999999;">
                If you have any questions about this receipt, please contact us at {{ $businessEmail }}
                or call us at {{ $businessPhone }}.
            </p>
            <p style="margin: 0; font-size: 11px; color: #999999;">
                &copy; {{ date('Y') }} {{ $businessName }}. All rights reserved.
            </p>
        </td>
    </tr>
</table>

{{-- Footer --}}
@slot('footer')
@component('mail::footer')
© {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
@endcomponent
@endslot
@endcomponent
