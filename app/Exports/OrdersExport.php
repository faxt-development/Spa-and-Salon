<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{
    /**
     * The data collection for the export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Order::with(['client', 'items.service'])
            ->latest()
            ->get();
    }
    
    /**
     * Get the data for PDF export.
     *
     * @return array
     */
    public function getPdfData(): array
    {
        $orders = $this->collection();
        
        return [
            'orders' => $orders,
            'title' => 'Orders Report',
            'date' => now()->format('F j, Y'),
            'totalOrders' => $orders->count(),
            'totalRevenue' => $orders->sum('total'),
            'totalTax' => $orders->sum('tax'),
            'totalDiscount' => $orders->sum('discount'),
        ];
    }
    
    /**
     * Get the view for PDF export.
     *
     * @return string
     */
    public function getPdfView(): string
    {
        return 'exports.orders';
    }

    /**
     * The headings for the Excel export.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Order #',
            'Client',
            'Date',
            'Status',
            'Subtotal',
            'Tax',
            'Discount',
            'Total',
            'Payment Method',
            'Payment Status',
            'Items Count',
            'Created At',
        ];
    }
    
    /**
     * Column formats for the Excel export.
     *
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
            'F' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
            'G' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
            'H' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
            'L' => NumberFormat::FORMAT_DATE_DDMMYYYY . ' ' . NumberFormat::FORMAT_DATE_TIME3,
        ];
    }

    /**
     * Map the data for the Excel export.
     *
     * @param  mixed  $order
     * @return array
     */
    public function map($order): array
    {
        return [
            $order->order_number,
            $order->client->name ?? 'Walk-in',
            $order->created_at,
            ucfirst($order->status),
            $order->subtotal,
            $order->tax,
            $order->discount,
            $order->total,
            ucfirst($order->payment_method),
            ucfirst($order->payment_status),
            $order->items->sum('quantity'),
            $order->created_at,
        ];
    }
}
