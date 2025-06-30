<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Orders Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { text-align: right; font-size: 10px; margin-top: 20px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary { margin-top: 20px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .summary-label { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Orders Report</h2>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Client</th>
                <th>Date</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Tax</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Total</th>
                <th>Status</th>
                <th>Payment</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->client->name ?? 'Walk-in' }}</td>
                    <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                    <td class="text-right">{{ number_format($order->subtotal, 2) }}</td>
                    <td class="text-right">{{ number_format($order->tax, 2) }}</td>
                    <td class="text-right">{{ number_format($order->discount, 2) }}</td>
                    <td class="text-right">{{ number_format($order->total, 2) }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td class="text-center">
                        <span style="color: {{ $order->payment_status === 'paid' ? 'green' : 'red' }};">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </td>
                </tr>
                @if($order->items->isNotEmpty())
                    <tr>
                        <td colspan="9" style="padding: 5px 10px;">
                            <strong>Items:</strong>
                            <ul style="margin: 5px 0 0 20px; padding: 0;">
                                @foreach($order->items as $item)
                                    <li>
                                        {{ $item->quantity }}x {{ $item->service->name }} 
                                        @ ${{ number_format($item->price, 2) }}
                                        = ${{ number_format($item->quantity * $item->price, 2) }}
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row">
            <span class="summary-label">Total Orders:</span>
            <span>{{ $orders->count() }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Revenue:</span>
            <span>${{ number_format($orders->sum('total'), 2) }}</span>
        </div>
    </div>

    <div class="footer">
        <p>Page {PAGE_NUM} of {PAGE_COUNT}</p>
    </div>
</body>
</html>
