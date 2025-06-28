<?php

namespace App\Services;

use App\Models\ModelStateChange;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StateReplayService
{
    /**
     * Replay state changes for a specific model instance
     */
    public function replayForModel(Model $model, ?CarbonInterface $upTo = null): Model
    {
        return DB::transaction(function () use ($model, $upTo) {
            $query = $model->stateChanges()
                ->orderBy('created_at');

            if ($upTo) {
                $query->where('created_at', '<=', $upTo);
            }

            $stateChanges = $query->get();
            
            // Reset the model to its initial state
            $this->resetModelToInitialState($model);
            
            // Apply each state change in order
            foreach ($stateChanges as $change) {
                $this->applyStateChange($model, $change);
            }
            
            return $model->fresh();
        });
    }

    /**
     * Replay state changes for multiple models of the same type
     */
    public function replayForModelType(string $modelClass, ?CarbonInterface $upTo = null): Collection
    {
        if (!class_exists($modelClass) || !is_a($modelClass, Model::class, true)) {
            throw new \InvalidArgumentException("Invalid model class: {$modelClass}");
        }

        // Get all model IDs that have state changes
        $query = ModelStateChange::query()
            ->where('model_type', $modelClass)
            ->select('model_id')
            ->distinct();

        if ($upTo) {
            $query->where('created_at', '<=', $upTo);
        }

        $modelIds = $query->pluck('model_id');

        // Replay changes for each model
        return $modelClass::whereIn('id', $modelIds)
            ->get()
            ->map(fn ($model) => $this->replayForModel($model, $upTo));
    }

    /**
     * Get the state of a model at a specific point in time
     */
    public function getStateAt(Model $model, CarbonInterface $at): ?Model
    {
        // Create a fresh instance to avoid modifying the original model
        $replayedModel = $model->replicate();
        
        $stateChanges = $model->stateChanges()
            ->where('created_at', '<=', $at)
            ->orderBy('created_at')
            ->get();

        // If no state changes, return the model as-is
        if ($stateChanges->isEmpty()) {
            return $model->fresh();
        }

        // Reset and apply changes
        $this->resetModelToInitialState($replayedModel);
        
        foreach ($stateChanges as $change) {
            $this->applyStateChange($replayedModel, $change);
        }
        
        return $replayedModel->fresh();
    }

    /**
     * Get a diff between two points in time for a model
     */
    public function getDiffBetween(
        Model $model,
        CarbonInterface $from,
        CarbonInterface $to
    ): array {
        $initialState = $this->getStateAt($model, $from);
        $finalState = $this->getStateAt($model, $to);
        
        return $this->diffModels($initialState, $finalState);
    }

    /**
     * Reset a model to its initial state
     */
    protected function resetModelToInitialState(Model $model): void
    {
        // Get the first state change to determine the initial state
        $firstChange = $model->stateChanges()
            ->orderBy('created_at')
            ->first();

        if ($firstChange && $firstChange->event_type === 'created') {
            // If we have the creation event, use its data
            $model->setRawAttributes($firstChange->original);
        } else {
            // Otherwise, use the model's current state as the initial state
            $model->setRawAttributes($model->getAttributes());
        }
    }

    /**
     * Apply a single state change to a model
     */
    protected function applyStateChange(Model $model, ModelStateChange $change): void
    {
        switch ($change->event_type) {
            case 'created':
            case 'restored':
                $model->setRawAttributes($change->changes);
                break;
                
            case 'updated':
                $model->setRawAttributes(
                    array_merge($model->getAttributes(), $change->changes)
                );
                break;
                
            case 'deleted':
            case 'force_deleted':
                // For deletions, we might want to handle this differently
                // For now, we'll just skip these events during replay
                break;
        }
    }

    /**
     * Get the differences between two model states
     */
    protected function diffModels(?Model $old, ?Model $new): array
    {
        if (!$old && !$new) {
            return [];
        }

        if (!$old) {
            return ['created' => $new->toArray()];
        }

        if (!$new) {
            return ['deleted' => $old->toArray()];
        }

        $oldAttributes = $old->getAttributes();
        $newAttributes = $new->getAttributes();
        
        $diff = [];
        
        // Find changed attributes
        foreach ($newAttributes as $key => $value) {
            if (!array_key_exists($key, $oldAttributes) || $oldAttributes[$key] != $value) {
                $diff['changed'][$key] = [
                    'old' => $oldAttributes[$key] ?? null,
                    'new' => $value,
                ];
            }
        }
        
        // Find removed attributes
        foreach (array_diff_key($oldAttributes, $newAttributes) as $key => $value) {
            $diff['removed'][$key] = $value;
        }
        
        return $diff;
    }
}
