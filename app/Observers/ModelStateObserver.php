<?php

namespace App\Observers;

use App\Traits\TracksStateChanges;
use Illuminate\Database\Eloquent\Model;

class ModelStateObserver
{
    /**
     * Handle the model "created" event.
     */
    public function created(Model $model): void
    {
        $this->recordStateChange($model, 'created');
    }

    /**
     * Handle the model "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->recordStateChange($model, 'updated');
    }

    /**
     * Handle the model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->recordStateChange($model, 'deleted');
    }

    /**
     * Handle the model "restored" event.
     */
    public function restored(Model $model): void
    {
        $this->recordStateChange($model, 'restored');
    }

    /**
     * Handle the model "force deleted" event.
     */
    public function forceDeleted(Model $model): void
    {
        $this->recordStateChange($model, 'force_deleted');
    }

    /**
     * Record a state change for the model
     */
    protected function recordStateChange(Model $model, string $eventType): void
    {
        // Only record if the model uses the TracksStateChanges trait
        if (!in_array(TracksStateChanges::class, class_uses_recursive($model))) {
            return;
        }

        // Delegate to the trait's method
        $model->recordStateChange($eventType);
    }
}
