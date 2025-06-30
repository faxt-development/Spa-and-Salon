<?php

namespace App\Exports;

use App\Models\Appointment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AppointmentsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{
    /**
     * The data collection for the export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Appointment::with(['client', 'services', 'staff'])
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
        return [
            'appointments' => $this->collection(),
            'title' => 'Appointments Report',
            'date' => now()->format('F j, Y'),
        ];
    }
    
    /**
     * Get the view for PDF export.
     *
     * @return string
     */
    public function getPdfView(): string
    {
        return 'exports.appointments';
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
            'Client',
            'Staff',
            'Services',
            'Start Time',
            'End Time',
            'Duration',
            'Status',
            'Notes',
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
            'E' => NumberFormat::FORMAT_DATE_DDMMYYYY . ' ' . NumberFormat::FORMAT_DATE_TIME3,
            'F' => NumberFormat::FORMAT_DATE_DDMMYYYY . ' ' . NumberFormat::FORMAT_DATE_TIME3,
            'G' => NumberFormat::FORMAT_NUMBER_00 . '" min"',
            'J' => NumberFormat::FORMAT_DATE_DDMMYYYY . ' ' . NumberFormat::FORMAT_DATE_TIME3,
        ];
    }

    /**
     * Map the data for the Excel export.
     *
     * @param  mixed  $appointment
     * @return array
     */
    public function map($appointment): array
    {
        $startTime = $appointment->start_time;
        $endTime = $appointment->end_time;
        $duration = $startTime && $endTime ? $startTime->diffInMinutes($endTime) : 0;
        
        return [
            $appointment->id,
            $appointment->client->name ?? 'N/A',
            $appointment->staff->name ?? 'N/A',
            $appointment->services->pluck('name')->implode(', '),
            $startTime,
            $endTime,
            $duration,
            ucfirst($appointment->status),
            $appointment->notes,
            $appointment->created_at,
        ];
    }
}
