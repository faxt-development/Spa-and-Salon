<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = [
    'Appointment',
    'Client',
    'DripCampaignRecipient',
    'DripCampaign',
    'EmailCampaign',
    'EmailRecipient',
    'Employee',
    'GiftCard',
    'Inventory',
    'InventoryTransaction',
    'OrderItem',
    'Order',
    'Payment',
    'PayrollRecord',
    'Permission',
    'ProductCategory',
    'Product',
    'PromotionUsage',
    'Promotion',
    'RevenueEvent',
    'Role',
    'Room',
    'ServiceCategory',
    'Service',
    'Setting',
    'Staff',
    'Subscription',
    'Supplier',
    'TaxRate',
    'TimeClockEntry',
    'TransactionLineItem',
    'Transaction',
    'User',
    'WalkIn'
];

$results = [];

echo "Checking models for SoftDeletes trait...\n\n";

foreach ($tables as $modelName) {
    $modelClass = "App\\Models\\{$modelName}";
    
    if (!class_exists($modelClass)) {
        $results[$modelName] = 'Model not found';
        continue;
    }
    
    $reflection = new ReflectionClass($modelClass);
    $traitNames = $reflection->getTraitNames();
    
    $hasSoftDeletes = in_array('Illuminate\\Database\\Eloquent\\SoftDeletes', $traitNames) || 
                     in_array('Illuminate\\Database\\Eloquent\\SoftDeletes', array_map('trait_short_name', $traitNames));
    
    $results[$modelName] = $hasSoftDeletes ? 'Has SoftDeletes' : 'Needs SoftDeletes';
    
    if (!$hasSoftDeletes) {
        $modelPath = app_path("Models/{$modelName}.php");
        if (file_exists($modelPath)) {
            $content = file_get_contents($modelPath);
            $updatedContent = add_soft_deletes_trait($content, $modelName);
            
            if ($content !== $updatedContent) {
                file_put_contents($modelPath, $updatedContent);
                $results[$modelName] = 'Added SoftDeletes';
            }
        }
    }
    
    echo "{$modelName}: {$results[$modelName]}\n";
}

function trait_short_name($traitName) {
    $parts = explode('\\', $traitName);
    return end($parts);
}

function add_soft_deletes_trait($content, $modelName) {
    // Check if already has SoftDeletes
    if (strpos($content, 'use Illuminate\\Database\\Eloquent\\SoftDeletes;') !== false) {
        return $content;
    }
    
    // Add use statement
    $content = str_replace(
        'namespace App\\Models;',
        "namespace App\\Models;\n\nuse Illuminate\\Database\\Eloquent\\SoftDeletes;",
        $content
    );
    
    // Add trait to class
    $content = preg_replace(
        "/class {$modelName}.*?\\{/s",
        "class {$modelName} extends Model\n{\n    use SoftDeletes;\n",
        $content
    );
    
    return $content;
}

echo "\nAll models have been processed.\n";
