<?php

use App\Http\Controllers\Admin\ClientReportController;
use Illuminate\Support\Facades\Route;

// Test route for client reports
Route::get('/test/client-reports', function () {
    $controller = new ClientReportController();
    
    // Test index method
    $indexResponse = $controller->index();
    
    // Test export method
    $exportResponse = $controller->export();
    
    // Test exportSingle method with a sample client ID (you may need to adjust this)
    $client = \App\Models\Client::first();
    $exportSingleResponse = $client ? $controller->exportSingle($client) : 'No clients found';
    
    return [
        'index' => $indexResponse->getContent(),
        'export' => $exportResponse->headers->get('content-type') === 'text/csv' ? 'CSV export successful' : 'CSV export failed',
        'export_single' => is_string($exportSingleResponse) ? $exportSingleResponse : 'Single client export successful'
    ];
});
