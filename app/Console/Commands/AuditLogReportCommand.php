<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use SplTempFileObject;

class AuditLogReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:report
                            {--start= : Start date (Y-m-d)}
                            {--end= : End date (Y-m-d, defaults to today)}
                            {--user= : Filter by user ID}
                            {--action= : Filter by action}
                            {--auditable-type= : Filter by auditable type}
                            {--output= : Output format (csv, json, table) - defaults to table}
                            {--file= : Output file path for CSV/JSON export} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a report of audit log entries';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startDate = $this->option('start') 
            ? Carbon::parse($this->option('start'))
            : now()->subDays(30);
            
        $endDate = $this->option('end')
            ? Carbon::parse($this->option('end'))
            : now();
            
        $query = AuditLog::with(['user', 'auditable', 'related'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc');
            
        // Apply filters
        if ($userId = $this->option('user')) {
            $query->where('user_id', $userId);
        }
        
        if ($action = $this->option('action')) {
            $query->where('action', 'like', "%{$action}%");
        }
        
        if ($auditableType = $this->option('auditable-type')) {
            $query->where('auditable_type', $auditableType);
        }
        
        $logs = $query->get();
        $outputFormat = $this->option('output') ?: 'table';
        
        if ($logs->isEmpty()) {
            $this->info('No audit log entries found matching the criteria.');
            return 0;
        }
        
        switch (strtolower($outputFormat)) {
            case 'csv':
                $this->exportToCsv($logs);
                break;
                
            case 'json':
                $this->exportToJson($logs);
                break;
                
            case 'table':
            default:
                $this->displayAsTable($logs);
                break;
        }
        
        return 0;
    }
    
    /**
     * Display the logs in a table.
     */
    protected function displayAsTable($logs): void
    {
        $this->info("Found {$logs->count()} audit log entries");
        
        $headers = [
            'ID', 'Date', 'User', 'Action', 'Description', 'Auditable', 'Related'
        ];
        
        $rows = $logs->map(function ($log) {
            return [
                $log->id,
                $log->created_at->format('Y-m-d H:i:s'),
                $log->user ? $log->user->name : 'System',
                $log->action,
                Str::limit($log->description, 50),
                $log->auditable_type ? "{$log->auditable_type} #{$log->auditable_id}" : 'N/A',
                $log->related_type ? "{$log->related_type} #{$log->related_id}" : 'N/A',
            ];
        });
        
        $this->table($headers, $rows);
    }
    
    /**
     * Export the logs to a CSV file.
     */
    protected function exportToCsv($logs): void
    {
        $filename = $this->option('file') ?: 'audit-logs-' . now()->format('Y-m-d-His') . '.csv';
        
        $csv = Writer::createFromFileObject(new SplTempFileObject());
        
        // Add headers
        $csv->insertOne([
            'ID', 'Date', 'User ID', 'User Name', 'Action', 'Description', 
            'Auditable Type', 'Auditable ID', 'Related Type', 'Related ID', 
            'IP Address', 'User Agent', 'Metadata'
        ]);
        
        // Add rows
        foreach ($logs as $log) {
            $csv->insertOne([
                $log->id,
                $log->created_at->toDateTimeString(),
                $log->user_id,
                $log->user ? $log->user->name : '',
                $log->action,
                $log->description,
                $log->auditable_type,
                $log->auditable_id,
                $log->related_type,
                $log->related_id,
                $log->ip_address,
                $log->user_agent,
                json_encode($log->metadata, JSON_UNESCAPED_SLASHES)
            ]);
        }
        
        // Save to storage if a file path is provided
        if (strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            $directory = dirname($filename);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            file_put_contents($filename, $csv->getContent());
        } else {
            Storage::put($filename, $csv->getContent());
            $filename = storage_path('app/' . $filename);
        }
        
        $this->info("Audit log report exported to: {$filename}");
    }
    
    /**
     * Export the logs to a JSON file.
     */
    protected function exportToJson($logs): void
    {
        $filename = $this->option('file') ?: 'audit-logs-' . now()->format('Y-m-d-His') . '.json';
        
        $data = $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'created_at' => $log->created_at->toDateTimeString(),
                'user_id' => $log->user_id,
                'user_name' => $log->user ? $log->user->name : null,
                'action' => $log->action,
                'description' => $log->description,
                'auditable_type' => $log->auditable_type,
                'auditable_id' => $log->auditable_id,
                'related_type' => $log->related_type,
                'related_id' => $log->related_id,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'metadata' => $log->metadata,
            ];
        });
        
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        // Save to storage if a file path is provided
        if (strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            $directory = dirname($filename);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            file_put_contents($filename, $json);
        } else {
            Storage::put($filename, $json);
            $filename = storage_path('app/' . $filename);
        }
        
        $this->info("Audit log report exported to: {$filename}");
    }
}
