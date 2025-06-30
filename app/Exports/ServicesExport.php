<?php

namespace App\Exports;

use App\Models\Service;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ServicesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{
    /**
     * The data collection for the export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Service::with('category')
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Get the data for PDF export.
     *
     * @return array
     */
    public function getPdfData(): array
    {
        return [
            'services' => $this->collection(),
            'title' => 'Services Report',
            'date' => now()->format('F j, Y'),
            'totalServices' => $this->collection()->count(),
            'totalRevenue' => $this->collection()->sum('price'),
        ];
    }
    
    /**
     * Get the view for PDF export.
     *
     * @return string
     */
    public function getPdfView(): string
    {
        return 'exports.services';
    }

    /**
     * The headings for the Excel export.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Description',
            'Price',
            'Duration (min)',
            'Category',
            'Active',
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
            'D' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
            'E' => '0 "min"',
            'H' => NumberFormat::FORMAT_DATE_DDMMYYYY . ' ' . NumberFormat::FORMAT_DATE_TIME3,
        ];
    }

    /**
     * Map the data for the Excel export.
     *
     * @param  mixed  $service
     * @return array
     */
    public function map($service): array
    {
        return [
            $service->id,
            $service->name,
            $service->description,
            $service->price,
            $service->duration,
            $service->category->name ?? 'Uncategorized',
            $service->is_active ? 'Yes' : 'No',
            $service->created_at,
        ];
    }
}
