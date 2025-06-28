<?php

namespace App\Console\Commands;

use App\Services\StateReplayService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ReplayStateChanges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'state:replay 
                            {model : The model class to replay state changes for} 
                            {--id= : The ID of a specific model instance to replay} 
                            {--until=now : The date/time to replay changes until (any valid strtotime string)} 
                            {--dry-run : Don\'t save the changes, just show what would be done}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replay state changes for a model up to a specific point in time';

    /**
     * Execute the console command.
     */
    public function handle(StateReplayService $replayService)
    {
        $modelClass = $this->argument('model');
        $modelId = $this->option('id');
        $until = $this->option('until');
        $dryRun = $this->option('dry-run');

        // Validate model class
        if (!class_exists($modelClass) || !is_subclass_of($modelClass, Model::class)) {
            $this->error("The specified model class '{$modelClass}' does not exist or is not a valid Eloquent model.");
            return 1;
        }

        // Parse the until date
        try {
            $untilDate = $until === 'now' ? now() : Carbon::parse($until);
        } catch (\Exception $e) {
            $this->error("Invalid date format for --until: {$e->getMessage()}");
            return 1;
        }

        $this->info("Replaying state changes for {$modelClass}" . ($modelId ? " (ID: {$modelId})" : '') . " until {$untilDate}");

        try {
            if ($modelId) {
                // Replay for a specific model instance
                $model = $modelClass::findOrFail($modelId);
                
                if ($dryRun) {
                    $this->info("[DRY RUN] Would replay changes for {$modelClass} #{$modelId}");
                    $this->showStateDiff($model, $replayService, $untilDate);
                } else {
                    $this->info("Replaying changes for {$modelClass} #{$modelId}");
                    $replayedModel = $replayService->replayForModel($model, $untilDate);
                    $this->info("Successfully replayed changes for {$modelClass} #{$modelId}");
                }
            } else {
                // Replay for all instances of the model
                $models = $modelClass::all();
                
                if ($dryRun) {
                    $this->info("[DRY RUN] Would replay changes for {$models->count()} {$modelClass} models");
                    $models->each(function ($model) use ($replayService, $untilDate) {
                        $this->showStateDiff($model, $replayService, $untilDate);
                    });
                } else {
                    $bar = $this->output->createProgressBar($models->count());
                    
                    $models->each(function ($model) use ($replayService, $untilDate, $bar) {
                        try {
                            $replayService->replayForModel($model, $untilDate);
                            $bar->advance();
                        } catch (\Exception $e) {
                            Log::error("Failed to replay state changes for model {$model->getKey()}: " . $e->getMessage());
                            $this->error("Error processing model ID {$model->getKey()}: " . $e->getMessage());
                        }
                    });
                    
                    $bar->finish();
                    $this->newLine(2);
                    $this->info("Successfully replayed changes for {$models->count()} {$modelClass} models");
                }
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error("State replay failed: " . $e->getMessage(), [
                'exception' => $e,
                'model' => $modelClass,
                'model_id' => $modelId ?? null,
                'until' => $until,
            ]);
            return 1;
        }
    }
    
    /**
     * Show the difference between current state and replayed state
     */
    protected function showStateDiff(Model $model, StateReplayService $replayService, $untilDate): void
    {
        $currentState = $model->getAttributes();
        $replayedState = $replayService->getStateAt($model, $untilDate);
        
        $diff = [];
        foreach ($currentState as $key => $value) {
            $newValue = $replayedState->$key ?? null;
            if ($value != $newValue) {
                $diff[] = [
                    'Attribute' => $key,
                    'Current' => is_array($value) ? json_encode($value) : $value,
                    'After Replay' => is_array($newValue) ? json_encode($newValue) : $newValue,
                ];
            }
        }
        
        if (empty($diff)) {
            $this->line("  No changes for model ID {$model->getKey()}");
        } else {
            $this->line("  Changes for model ID {$model->getKey()}:");
            $this->table(['Attribute', 'Current', 'After Replay'], $diff);
        }
    }
}
