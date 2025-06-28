<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log an action to the audit log.
     *
     * @param string $action
     * @param string $description
     * @param Model|string|null $auditable
     * @param Model|string|array|null $related
     * @param array $metadata
     * @param \DateTimeInterface|null $occurredAt
     * @return AuditLog
     */
    public function log(
        string $action,
        string $description,
        $auditable = null,
        $related = null,
        array $metadata = [],
        ?\DateTimeInterface $occurredAt = null
    ): AuditLog {
        $userId = Auth::id();
        $auditData = [
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'metadata' => $metadata,
            'occurred_at' => $occurredAt,
        ];

        // Handle auditable model
        if ($auditable instanceof Model) {
            $auditData['auditable_type'] = get_class($auditable);
            $auditData['auditable_id'] = $auditable->getKey();
        } elseif (is_string($auditable)) {
            $auditData['auditable_type'] = $auditable;
        }

        // Handle related model
        if ($related instanceof Model) {
            $auditData['related_type'] = get_class($related);
            $auditData['related_id'] = $related->getKey();
        } elseif (is_array($related) && count($related) === 2) {
            $auditData['related_type'] = $related[0];
            $auditData['related_id'] = $related[1];
        } elseif (is_string($related)) {
            $auditData['related_type'] = $related;
        }

        return AuditLog::create($auditData);
    }

    /**
     * Log a model event.
     *
     * @param string $event
     * @param Model $model
     * @param array $metadata
     * @return AuditLog
     */
    public function logModelEvent(string $event, Model $model, array $metadata = []): AuditLog
    {
        $modelName = class_basename($model);
        $description = "{$modelName} #{$model->getKey()} was {$event}";
        
        if ($event === 'updated' && $model->wasChanged()) {
            $metadata['changes'] = $model->getChanges();
        }
        
        return $this->log(
            action: "model.{$event}",
            description: $description,
            auditable: $model,
            metadata: $metadata
        );
    }

    /**
     * Log a user action.
     *
     * @param string $action
     * @param string $description
     * @param Model|string|null $auditable
     * @param array $metadata
     * @return AuditLog
     */
    public function logUserAction(
        string $action,
        string $description,
        $auditable = null,
        array $metadata = []
    ): AuditLog {
        return $this->log(
            action: "user.{$action}",
            description: $description,
            auditable: $auditable,
            metadata: $metadata
        );
    }

    /**
     * Log a system event.
     *
     * @param string $event
     * @param string $description
     * @param array $metadata
     * @return AuditLog
     */
    public function logSystemEvent(string $event, string $description, array $metadata = []): AuditLog
    {
        return $this->log(
            action: "system.{$event}",
            description: $description,
            metadata: $metadata
        );
    }

    /**
     * Get audit logs with optional filtering.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function queryLogs(array $filters = [])
    {
        $query = AuditLog::query()->with(['user', 'auditable', 'related']);

        if (!empty($filters['action'])) {
            $query->where('action', 'like', "%{$filters['action']}%");
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['auditable_type'])) {
            $query->where('auditable_type', $filters['auditable_type']);
        }

        if (!empty($filters['auditable_id'])) {
            $query->where('auditable_id', $filters['auditable_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->where('occurred_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('occurred_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }

        // Default sorting
        $query->latest('occurred_at');

        return $query;
    }
}
