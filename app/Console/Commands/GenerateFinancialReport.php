<?php

namespace App\Console\Commands;

use App\Facades\FinancialReports;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use SplTempFileObject;

class GenerateFinancialReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:financial 
                            {report : The type of report to generate (summary, revenue-by-period, revenue-by-category, revenue-by-staff, revenue-by-location, top-services, top-products, top-customers, commission, sales-tax, tips, discounts, time-of-day)}
                            {--start= : Start date (YYYY-MM-DD) - defaults to 30 days ago}
                            {--end= : End date (YYYY-MM-DD) - defaults to today}
                            {--period= : For revenue-by-period report: daily, weekly, monthly, yearly}
                            {--limit=10 : For top-N reports, number of results to return}
                            {--location= : Filter by location ID}
                            {--staff= : Filter by staff ID}
                            {--service= : Filter by service ID}
                            {--product= : Filter by product ID}
                            {--category= : Filter by category ID}
                            {--output= : Output format (table, csv, json) - defaults to table}
                            {--file= : Output file path for CSV/JSON export} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate financial reports';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $reportType = $this->argument('report');
        $startDate = $this->option('start') 
            ? Carbon::parse($this->option('start'))
            : now()->subDays(30);
            
        $endDate = $this->option('end')
            ? Carbon::parse($this->option('end'))
            : now();
            
        $filters = $this->getFilters();
        $outputFormat = $this->option('output') ?: 'table';
        
        $this->info("Generating {$reportType} report from {$startDate->toDateString()} to {$endDate->toDateString()}");
        
        try {
            $data = $this->generateReport($reportType, $startDate, $endDate, $filters);
            
            if (empty($data)) {
                $this->warn('No data found for the specified criteria.');
                return 0;
            }
            
            $this->outputReport($reportType, $data, $outputFormat);
            
        } catch (\Exception $e) {
            $this->error("Error generating report: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
    
    /**
     * Generate the requested report
     */
    protected function generateReport(string $reportType, Carbon $startDate, Carbon $endDate, array $filters)
    {
        return match ($reportType) {
            'summary' => FinancialReports::getRevenueSummary($startDate, $endDate, $filters),
            'revenue-by-period' => FinancialReports::getRevenueByPeriod(
                $startDate, 
                $endDate, 
                $this->option('period') ?: 'daily',
                $filters
            ),
            'revenue-by-category' => FinancialReports::getRevenueByCategory($startDate, $endDate, $filters),
            'revenue-by-staff' => FinancialReports::getRevenueByStaff($startDate, $endDate, $filters),
            'revenue-by-location' => FinancialReports::getRevenueByLocation($startDate, $endDate, $filters),
            'top-services' => FinancialReports::getTopServices(
                $startDate, 
                $endDate, 
                (int)$this->option('limit'), 
                $filters
            ),
            'top-products' => FinancialReports::getTopProducts(
                $startDate, 
                $endDate, 
                (int)$this->option('limit'), 
                $filters
            ),
            'top-customers' => FinancialReports::getTopCustomers(
                $startDate, 
                $endDate, 
                (int)$this->option('limit'), 
                $filters
            ),
            'commission' => FinancialReports::getCommissionReport($startDate, $endDate, $filters),
            'sales-tax' => FinancialReports::getSalesTaxReport($startDate, $endDate, $filters),
            'tips' => FinancialReports::getTipsReport($startDate, $endDate, $filters),
            'discounts' => FinancialReports::getDiscountsReport($startDate, $endDate, $filters),
            'time-of-day' => FinancialReports::getRevenueByTimeOfDay($startDate, $endDate, $filters),
            default => throw new \InvalidArgumentException("Unknown report type: {$reportType}"),
        };
    }
    
    /**
     * Output the report in the requested format
     */
    protected function outputReport(string $reportType, $data, string $format): void
    {
        if (in_array($format, ['csv', 'json'])) {
            $filename = $this->getOutputFilename($reportType, $format);
            
            if ($format === 'csv') {
                $this->exportToCsv($data, $filename);
            } else {
                $this->exportToJson($data, $filename);
            }
            
            $this->info("Report exported to: {$filename}");
        } else {
            // Default to table output
            if (is_array($data) && !empty($data)) {
                $this->displayAsTable([$data]);
            } elseif (is_object($data) && method_exists($data, 'toArray')) {
                $this->displayAsTable([$data->toArray()]);
            } else {
                $this->displayAsTable($data);
            }
        }
    }
    
    /**
     * Get the output filename for the report
     */
    protected function getOutputFilename(string $reportType, string $format): string
    {
        $filename = $this->option('file') 
            ?: 'financial-reports/' . str_replace(' ', '-', $reportType) . '-' . now()->format('Y-m-d-His') . '.' . $format;
        
        // Ensure directory exists
        $directory = dirname($filename);
        if (!is_dir($directory) && !in_array($directory, ['.', ''])) {
            mkdir($directory, 0755, true);
        }
        
        return $filename;
    }
    
    /**
     * Display data as a table
     */
    protected function displayAsTable(iterable $data): void
    {
        if (empty($data)) {
            $this->info('No data to display.');
            return;
        }
        
        // Convert objects to arrays
        $items = collect($data)->map(function ($item) {
            if (is_array($item)) {
                return $item;
            }
            
            if (method_exists($item, 'toArray')) {
                return $item->toArray();
            }
            
            return (array) $item;
        })->toArray();
        
        if (empty($items)) {
            $this->info('No data to display.');
            return;
        }
        
        // Get all possible keys from all items
        $allKeys = [];
        foreach ($items as $item) {
            $allKeys = array_merge($allKeys, array_keys($item));
        }
        $allKeys = array_unique($allKeys);
        
        // Filter out any keys that are objects or arrays (for display purposes)
        $headers = [];
        $rows = [];
        
        foreach ($items as $item) {
            $row = [];
            foreach ($allKeys as $key) {
                $value = $item[$key] ?? null;
                
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value, JSON_PRETTY_PRINT);
                } elseif (is_bool($value)) {
                    $value = $value ? 'Yes' : 'No';
                } elseif (is_null($value)) {
                    $value = '';
                }
                
                $row[$key] = $value;
                
                if (!in_array($key, $headers)) {
                    $headers[] = $key;
                }
            }
            $rows[] = $row;
        }
        
        // Format headers for display
        $formattedHeaders = array_map(function ($header) {
            return ucwords(str_replace('_', ' ', $header));
        }, $headers);
        
        $this->table($formattedHeaders, $rows);
    }
    
    /**
     * Export data to CSV
     */
    protected function exportToCsv($data, string $filename): void
    {
        $csv = Writer::createFromFileObject(new SplTempFileObject());
        
        // Handle single record (non-collection)
        if (!is_iterable($data) || is_array($data) && array_keys($data) !== range(0, count($data) - 1)) {
            $data = [$data];
        }
        
        $firstRow = true;
        
        foreach ($data as $item) {
            if (is_object($item) && method_exists($item, 'toArray')) {
                $item = $item->toArray();
            } elseif (is_object($item)) {
                $item = (array) $item;
            }
            
            if ($firstRow) {
                $headers = array_keys($item);
                $csv->insertOne($headers);
                $firstRow = false;
            }
            
            $csv->insertOne($item);
        }
        
        file_put_contents($filename, $csv->getContent());
    }
    
    /**
     * Export data to JSON
     */
    protected function exportToJson($data, string $filename): void
    {
        if (is_object($data) && method_exists($data, 'toArray')) {
            $data = $data->toArray();
        } elseif (is_object($data)) {
            $data = (array) $data;
        }
        
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($filename, $json);
    }
    
    /**
     * Get filters from command options
     */
    protected function getFilters(): array
    {
        $filters = [];
        
        if ($this->option('location')) {
            $filters['location_id'] = $this->option('location');
        }
        
        if ($this->option('staff')) {
            $filters['staff_id'] = $this->option('staff');
        }
        
        if ($this->option('service')) {
            $filters['service_id'] = $this->option('service');
        }
        
        if ($this->option('product')) {
            $filters['product_id'] = $this->option('product');
        }
        
        if ($this->option('category')) {
            $filters['category_id'] = $this->option('category');
        }
        
        return $filters;
    }
}
