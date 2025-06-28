<?php

namespace App\Traits;

use App\Events\ModelStateChanged;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Trait TracksStateChanges
 * 
 * Adds event sourcing capabilities to models by tracking all state changes
 */
trait TracksStateChanges
{
    /**
     * Boot the trait
     */
    protected static function bootTracksStateChanges()
    {
        static::saving(function (Model $model) {
            if (!$model->exists) {
                $model->recordStateChange('created');
            } else {
                $model->recordStateChange('updated');
            }
        });

        static::deleting(function (Model $model) {
            if ($model->isForceDeleting()) {
                $model->recordStateChange('force_deleted');
            } else {
                $model->recordStateChange('deleted');
            }
        });

        static::restored(function (Model $model) {
            $model->recordStateChange('restored');
        });
    }

    /**
     * Record a state change event
     */
    protected function recordStateChange(string $eventType): void
    {
        // Don't record if nothing changed (except for created/deleted events)
        if ($eventType === 'updated' && !$this->isDirty()) {
            return;
        }

        $changes = $eventType === 'created' 
            ? $this->getAttributes() 
            : $this->getDirty();

        // Don't record empty changes
        if (empty($changes) && !in_array($eventType, ['created', 'deleted', 'restored', 'force_deleted'])) {
            return;
        }

        // If this is a create event and the model doesn't have an ID yet,
        // we'll queue the state change to be recorded after the model is saved
        if ($eventType === 'created' && !$this->exists) {
            static::created(function ($model) use ($eventType, $changes) {
                $this->recordStateChangeForModel($model, $eventType, $changes);
            });
            return;
        }

        $this->recordStateChangeForModel($this, $eventType, $changes);
    }

    /**
     * Record a state change for a model that has been saved
     */
    protected function recordStateChangeForModel($model, string $eventType, array $changes): void
    {
        $userId = Auth::id();
        $ipAddress = request()?->ip();
        $userAgent = request()?->userAgent();

        $eventData = [
            'event_type' => $eventType,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'changes' => $changes,
            'original' => $model->getOriginal() ?? [],
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'occurred_at' => now(),
        ];

        // Dispatch the event
        event(new ModelStateChanged($eventData));

        // Also store in the database
        $model->stateChanges()->create([
            'event_type' => $eventType,
            'changes' => $changes,
            'original' => $model->getOriginal() ?? [],
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Get all state changes for the model
     */
    public function stateChanges()
    {
        return $this->morphMany(\App\Models\ModelStateChange::class, 'model');
    }

    /**
     * Replay state changes for all models of this type up to a specific point in time
     */
    public static function replayAllStateChanges(\DateTimeInterface $upTo = null)
    {
        $query = static::with('stateChanges')
            ->whereHas('stateChanges');

        if ($upTo) {
            $query->whereHas('stateChanges', function ($q) use ($upTo) {
                $q->where('created_at', '<=', $upTo);
            });
        }

        return $query->get()->each->replayStateChanges($upTo);
    }

    /**
     * Replay state changes for this instance
     */
    public function replayStateChanges(\DateTimeInterface $upTo = null)
    {
        $query = $this->stateChanges()->orderBy('created_at');
        
        if ($upTo) {
            $query->where('created_at', '<=', $upTo);
        }

        $stateChanges = $query->get();

        // Replay each state change
        foreach ($stateChanges as $change) {
            $this->applyStateChange($change);
        }

        return $this;
    }

    /**
     * Apply a single state change
     */
    protected function applyStateChange($change)
    {
        switch ($change->event_type) {
            case 'created':
            case 'restored':
                $this->setRawAttributes($change->changes);
                break;
                
            case 'updated':
                $this->setRawAttributes(
                    array_merge($this->getAttributes(), $change->changes)
                );
                break;
                
            case 'deleted':
            case 'force_deleted':
                // Handle deletion if needed
                break;
        }
    }
}
