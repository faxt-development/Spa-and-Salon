<?php

namespace App\Console\Commands;

use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateCommissionReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commissions:report 
                            {--start-date= : Start date (Y-m-d)} 
                            {--end-date= : End date (Y-m-d)} 
                            {--output= : Output file path} 
                            {--format=csv : Output format (csv, xlsx)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a commission report for staff members';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startDate = $this->option('start-date') 
            ? Carbon::parse($this->option('start-date'))
            : Carbon::now()->startOfMonth();
            
        $endDate = $this->option('end-date')
            ? Carbon::parse($this->option('end-date'))
            : Carbon::now()->endOfMonth();
            
        $format = $this->option('format');
        $outputPath = $this->option('output') ?? 'commissions_' . now()->format('Y-m-d_His') . '.' . $format;
        
        $this->info(sprintf(
            'Generating commission report from %s to %s',
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ));
        
        // Get staff with their commission data
        $staff = Staff::with(['commissionStructure', 'commissionPayments' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function($q) use ($startDate, $endDate) {
                      $q->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                  });
        }])
        ->whereHas('commissionPayments', function($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function($q) use ($startDate, $endDate) {
                      $q->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                  });
        })
        ->get();
        
        if ($staff->isEmpty()) {
            $this->warn('No commission data found for the specified period.');
            return Command::FAILURE;
        }
        
        // Prepare report data
        $reportData = [];
        $grandTotal = 0;
        
        foreach ($staff as $member) {
            $totalCommission = $member->commissionPayments->sum('amount');
            $grandTotal += $totalCommission;
            
            $reportData[] = [
                'Staff ID' => $member->id,
                'Staff Name' => $member->full_name,
                'Position' => $member->position,
                'Commission Structure' => $member->commissionStructure?->name ?? 'Default',
                'Commission Rate' => $member->commission_rate . '%',
                'Total Commission' => number_format($totalCommission, 2),
                'Payment Status' => $member->commissionPayments->pluck('status')->unique()->implode(', '),
                'Payment Count' => $member->commissionPayments->count(),
            ];
        }
        
        // Sort by total commission (descending)
        usort($reportData, function($a, $b) {
            return floatval(str_replace(',', '', $b['Total Commission'])) <=> floatval(str_replace(',', '', $a['Total Commission']));
        });
        
        // Add grand total row
        $reportData[] = [
            'Staff ID' => '',
            'Staff Name' => 'GRAND TOTAL',
            'Position' => '',
            'Commission Structure' => '',
            'Commission Rate' => '',
            'Total Commission' => number_format($grandTotal, 2),
            'Payment Status' => '',
            'Payment Count' => $staff->sum(fn($s) => $s->commissionPayments->count()),
        ];
        
        // Generate the report
        $success = $this->generateReport($reportData, $outputPath, $format);
        
        if ($success) {
            $this->info(sprintf('Report generated successfully: %s', $outputPath));
            return Command::SUCCESS;
        } else {
            $this->error('Failed to generate report');
            return Command::FAILURE;
        }
    }
    
    /**
     * Generate the report in the specified format
     */
    protected function generateReport(array $data, string $outputPath, string $format): bool
    {
        try {
            if ($format === 'xlsx') {
                return $this->generateExcelReport($data, $outputPath);
            } else {
                return $this->generateCsvReport($data, $outputPath);
            }
        } catch (\Exception $e) {
            $this->error('Error generating report: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate an Excel report
     */
    protected function generateExcelReport(array $data, string $outputPath): bool
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $headers = array_keys($data[0]);
        $sheet->fromArray([$headers], null, 'A1');
        
        // Set data
        $rowData = array_map('array_values', $data);
        $sheet->fromArray($rowData, null, 'A2');
        
        // Style header row
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DDDDDD']
            ]
        ]);
        
        // Style grand total row
        if (count($data) > 1) {
            $lastRow = count($data) + 1;
            $sheet->getStyle('A' . $lastRow . ':' . $sheet->getHighestColumn() . $lastRow)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFFF99']
                ]
            ]);
        }
        
        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Save the file
        $writer = new Xlsx($spreadsheet);
        $writer->save($outputPath);
        
        return true;
    }
    
    /**
     * Generate a CSV report
     */
    protected function generateCsvReport(array $data, string $outputPath): bool
    {
        $handle = fopen($outputPath, 'w');
        
        if ($handle === false) {
            return false;
        }
        
        // Write headers
        fputcsv($handle, array_keys($data[0]));
        
        // Write data
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
        return true;
    }
}
