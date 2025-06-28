<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogHttpRequests
{
    /**
     * The audit log service instance.
     */
    protected AuditLogService $auditLogService;

    /**
     * Routes that should be excluded from logging.
     */
    protected array $except = [
        'horizon*',
        'telescope*',
        'livewire/*',
        '_debugbar/*',
        'api/*',
    ];

    /**
     * HTTP methods that should be logged.
     */
    protected array $methods = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * Create a new middleware instance.
     */
    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldLog($request)) {
            $this->logRequest($request);
        }

        return $next($request);
    }

    /**
     * Log the request.
     */
    protected function logRequest(Request $request): void
    {
        $route = $request->route();
        $action = $route ? $route->getActionName() : 'Closure';
        
        // Skip logging for specific actions
        if (Str::startsWith($action, 'Laravel')) {
            return;
        }

        $description = $this->getActionDescription($request);
        $metadata = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => optional($route)->getName(),
            'action' => $action,
            'parameters' => $route ? $route->parameters() : [],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'input' => $this->getFilteredInput($request),
        ];

        $this->auditLogService->log(
            action: 'http.request',
            description: $description,
            metadata: $metadata
        );
    }

    /**
     * Determine if the request should be logged.
     */
    protected function shouldLog(Request $request): bool
    {
        // Only log specific HTTP methods
        if (!in_array($request->method(), $this->methods)) {
            return false;
        }

        // Skip excluded paths
        foreach ($this->except as $except) {
            if ($request->is($except)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get a human-readable description of the action.
     */
    protected function getActionDescription(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();
        
        // Map common RESTful actions to human-readable descriptions
        $actionMap = [
            'POST' => 'Created',
            'PUT' => 'Updated',
            'PATCH' => 'Partially updated',
            'DELETE' => 'Deleted',
        ];
        
        $action = $actionMap[$method] ?? 'Performed action on';
        $resource = $this->getResourceNameFromPath($path);
        
        return "{$action} {$resource} via {$method} {$path}";
    }
    
    /**
     * Extract a resource name from the request path.
     */
    protected function getResourceNameFromPath(string $path): string
    {
        // Remove API prefix if present
        $path = preg_replace('#^api/#', '', $path);
        
        // Split by slashes and get the last non-empty segment
        $segments = array_filter(explode('/', $path));
        $lastSegment = end($segments);
        
        // Remove IDs and parameters
        $resource = preg_replace('/\d+$/', '', $lastSegment);
        $resource = str_replace('-', ' ', $resource);
        
        return ucfirst(trim($resource)) ?: 'resource';
    }
    
    /**
     * Get filtered input data, excluding sensitive fields.
     */
    protected function getFilteredInput(Request $request): array
    {
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'current_password',
            'credit_card',
            'cvv',
            'ssn',
            'token',
            'api_key',
            'secret',
        ];
        
        $input = $request->except($sensitiveFields);
        
        // Filter out any nested sensitive fields
        array_walk_recursive($input, function (&$value, $key) use ($sensitiveFields) {
            if (in_array(strtolower($key), $sensitiveFields)) {
                $value = '***REDACTED***';
            }
        });
        
        return $input;
    }
}
