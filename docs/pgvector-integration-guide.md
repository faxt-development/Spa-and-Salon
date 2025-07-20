# Laravel pgvector Integration Guide

This guide provides comprehensive instructions for integrating pgvector with Laravel, particularly when using Supabase PostgreSQL as your database. It addresses common issues and provides solutions for different PostgreSQL configurations, including challenging scenarios where standard vector casting approaches fail.

## Table of Contents
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Schema Detection](#schema-detection)
- [Vector Casting](#vector-casting)
- [Supabase RPC Approach](#supabase-rpc-approach)
- [Troubleshooting](#troubleshooting)
- [Testing pgvector Integration](#testing-pgvector-integration)
- [Common Issues](#common-issues)

## Prerequisites

Before integrating pgvector with Laravel, ensure you have:

1. A Laravel application (tested with Laravel 10+)
2. PostgreSQL database with pgvector extension installed
3. If using Supabase, ensure pgvector extension is enabled in your project

## Installation

### 1. Install the official pgvector Laravel package

```bash
composer require pgvector/pgvector
```

### 2. Create a Service Provider

Create a new service provider to register the pgvector package:

```bash
php artisan make:provider PgvectorServiceProvider
```

### 3. Implement the Service Provider

Edit the newly created provider at `app/Providers/PgvectorServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class PgvectorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the vendor's service provider
        $this->app->register(\Pgvector\Laravel\PgvectorServiceProvider::class);
        
        Log::info('PgvectorServiceProvider registered');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
```

### 4. Register the Service Provider

Add the service provider to the `providers` array in `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\PgvectorServiceProvider::class,
],
```

## Configuration

After installing the pgvector extension, you need to configure your Laravel application to use it correctly.

1. **Update your database configuration**

   Make sure your PostgreSQL connection is properly configured in your `.env` file:

   ```
   DB_CONNECTION=pgsql
   DB_HOST=your_postgres_host
   DB_PORT=5432
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

2. **Update the search path in database.php (critical for Supabase)**

   When using Supabase, the pgvector extension is installed in the `extensions` schema, which is not in the default search path. Add it to the search path in `config/database.php`:

   ```php
   'pgsql' => [
       // other configuration...
       'search_path' => 'public,extensions',
       // other configuration...
   ],
   ```

   After making this change, clear your configuration cache:

   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

3. **Create a migration for your embeddings table**

   ```php
   Schema::create('embeddings', function (Blueprint $table) {
       $table->id();
       $table->text('content');
       $table->jsonb('metadata')->nullable();
       $table->timestamps();
   });

   // Add vector column using raw SQL
   DB::statement("ALTER TABLE embeddings ADD COLUMN embedding vector(1536)");
   ```

4. **Install the Laravel pgvector package**

   ```bash
   composer require pgvector/pgvector
   ```

### Supabase Configuration

When using Supabase, you'll need to:

1. Enable the pgvector extension in your Supabase project
2. Use the connection details provided by Supabase
3. Be aware that Supabase may install the pgvector extension in a different schema (often `extensions` instead of `public`)

## Schema Detection

A critical aspect of pgvector integration is detecting which schema contains the vector extension. This varies between PostgreSQL installations, especially with hosted services like Supabase.

Here's a reliable way to detect the schema:

```php
try {
    $schemaCheck = DB::connection('pgsql')
        ->select("SELECT n.nspname as schema_name FROM pg_extension e JOIN pg_namespace n ON e.extnamespace = n.oid WHERE e.extname = 'vector'");
    
    $schema = !empty($schemaCheck) ? $schemaCheck[0]->schema_name : 'public';
    Log::info('Found vector extension in schema: ' . $schema);
    
    // Convert embedding array to SQL-friendly string with the correct schema
    $vectorCast = ($schema === 'public') ? '::vector' : "::$schema.vector";
    $vectorSql = "'[" . implode(',', $embedding) . "]'$vectorCast";
} catch (\Exception $e) {
    // If we can't determine the schema, default to public
    Log::warning('Could not determine vector schema, defaulting to public: ' . $e->getMessage());
    $vectorSql = "'[" . implode(',', $embedding) . "]'::vector";
}
```

## Vector Casting

Different PostgreSQL configurations require different vector casting syntaxes:

1. **Standard PostgreSQL**: `::vector`
2. **Qualified public schema**: `::public.vector`
3. **Extensions schema (common in Supabase)**: `::extensions.vector`

The correct syntax depends on:
1. Where the pgvector extension is installed
2. The current search path configuration
3. PostgreSQL version and configuration

## Supabase RPC Approach

When standard vector casting approaches fail with Supabase PostgreSQL, using PostgreSQL functions with RPC calls is a more reliable alternative. This approach works around the schema and search path issues by encapsulating vector operations within a database function.

### Why RPC Works When Direct Queries Fail

Supabase client libraries connect to PostgreSQL via PostgREST, which doesn't fully support pgvector similarity operators. By wrapping vector operations in a PostgreSQL function, we can ensure proper schema resolution and vector type handling.

### Creating a Vector Matching Function

```sql
create or replace function match_documents (
  query_embedding vector(384),
  match_threshold float,
  match_count int
)
returns table (
  id bigint,
  title text,
  body text,
  similarity float
)
language sql stable
as $$
  select
    documents.id,
    documents.title,
    documents.body,
    1 - (documents.embedding <=> query_embedding) as similarity
  from documents
  where 1 - (documents.embedding <=> query_embedding) > match_threshold
  order by (documents.embedding <=> query_embedding) asc
  limit match_count;
$$;
```

This function takes:
- `query_embedding`: The vector to compare against stored embeddings
- `match_threshold`: Minimum similarity threshold (e.g., 0.78)
- `match_count`: Maximum number of results to return

### Adapting for Laravel

In Laravel, you can execute this function using a raw query:

```php
$embedding = $this->getEmbedding($query); // Your embedding generation function
$embeddingString = '[' . implode(',', $embedding) . ']';

$results = DB::connection('pgsql')->select("
    SELECT * FROM match_documents(
        '$embeddingString'::vector,
        0.7,
        5
    )
");
```

Note that the vector casting happens inside the PostgreSQL function where the schema is properly resolved, avoiding the casting issues encountered with direct queries.

### Advantages of the RPC Approach

1. **Schema Independence**: Functions work regardless of search path settings
2. **Better Performance**: Can leverage indexes properly
3. **Simplified Client Code**: Encapsulates complex vector operations
4. **Consistent Results**: Avoids type casting issues

### Creating Laravel-Specific Helper Functions

For a Laravel application, you might create functions specifically for your embeddings table:

```sql
create or replace function match_embeddings (
  query_embedding vector(1536),
  match_threshold float,
  match_count int,
  source_type text
)
returns table (
  id bigint,
  content text,
  metadata jsonb,
  similarity float
)
language sql stable
as $$
  select
    embeddings.id,
    embeddings.content,
    embeddings.metadata,
    1 - (embeddings.embedding <=> query_embedding) as similarity
  from embeddings
  where metadata->>'source_type' = source_type
    and 1 - (embeddings.embedding <=> query_embedding) > match_threshold
  order by embeddings.embedding <=> query_embedding asc
  limit match_count;
$$;
```

### Vector Operators

Pgvector supports three distance operators:

| Operator | Description |
| --- | --- |
| <-> | Euclidean distance |
| <#> | Negative inner product |
| <=> | Cosine distance |

Choose the appropriate operator based on your embedding model and use case. For normalized vectors, cosine distance (`<=>`) is often preferred.

## Troubleshooting

### Common Error Messages

1. **"type 'vector' does not exist"**
   - The pgvector extension is not installed or not in the search path
   - Solution: Install pgvector or use fully qualified type name

2. **"operator does not exist: vector <=> vector"**
   - The vector operator is not recognized
   - Solution: Ensure pgvector extension is installed and use correct schema qualification

3. **"could not find function vector_l2_squared_distance"**
   - Internal pgvector function not found
   - Solution: Check pgvector installation and version

### Checking PostgreSQL Search Path

You can check your current search path with:

```sql
SHOW search_path;
```

In PHP:

```php
$result = DB::connection('pgsql')->select('SHOW search_path');
$searchPath = $result[0]->search_path;
```

### Checking pgvector Extension Installation

### Common Error Messages

1. **"type vector does not exist"**

   This error occurs when PostgreSQL cannot find the vector type. This usually happens because:
   
   - The pgvector extension is not installed
   - The pgvector extension is installed in a different schema that is not in your search path

   **Solutions**:
   
   **Option 1: Add the extension schema to your search path (Recommended)**
   
   Update your `config/database.php` file to include the extensions schema in the search path:
   
   ```php
   'pgsql' => [
       // other configuration...
       'search_path' => 'public,extensions',
       // other configuration...
   ],
   ```
   
   Then clear your configuration cache:
   
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```
   
   **Option 2: Use schema-qualified vector casting**
   
   Verify which schema the extension is installed in:

   ```sql
   SELECT n.nspname as schema_name 
   FROM pg_extension e 
   JOIN pg_namespace n ON e.extnamespace = n.oid 
   WHERE e.extname = 'vector';
   ```

   Then update your vector casting syntax to include the schema name:

   ```php
   $vectorSql = "'[" . implode(',', $embedding) . "]'::$schema.vector";
   ```

2. **"operator does not exist: vector <=> vector"**

   This error occurs when PostgreSQL can find the vector type but not the similarity operators. This usually happens because the operator is defined in a schema that's not in your search path.
   
   The solution is the same as above - add the extensions schema to your search path in `config/database.php`.

3. **"function vector_dims(vector) does not exist"**

   Similar to the above errors, this happens when PostgreSQL can't find the vector functions. Add the extensions schema to your search path.

## Testing pgvector Integration

We've created a test controller and view to help diagnose pgvector issues. This allows testing different vector casting syntaxes without server restarts.

### Test Controller

Create `app/Http/Controllers/PgvectorTestController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PgvectorTestController extends Controller
{
    /**
     * Show the pgvector test view
     */
    public function show()
    {
        return view('pgvector-test');
    }
    
    /**
     * Test pgvector functionality
     */
    public function test()
    {
        try {
            // Check if the pgvector extension is installed
            $extensionCheck = DB::connection('pgsql')
                ->select("SELECT * FROM pg_extension WHERE extname = 'vector'");
            
            if (empty($extensionCheck)) {
                return response()->json([
                    'success' => false,
                    'message' => 'pgvector extension is not installed in the database',
                    'database_info' => $this->getDatabaseInfo()
                ]);
            }
            
            // Check which schema the vector extension is in
            $schemaCheck = DB::connection('pgsql')
                ->select("SELECT n.nspname as schema_name FROM pg_extension e JOIN pg_namespace n ON e.extnamespace = n.oid WHERE e.extname = 'vector'");
            
            $schema = !empty($schemaCheck) ? $schemaCheck[0]->schema_name : 'public';
            
            // Try a simple vector operation using the detected schema
            $vectorCast = ($schema === 'public') ? '::vector' : "::$schema.vector";
            $query = "SELECT '[1,2,3]'$vectorCast <=> '[4,5,6]'$vectorCast AS distance";
            
            $result = DB::connection('pgsql')->select($query);
            
            return response()->json([
                'success' => true,
                'message' => 'pgvector is working correctly',
                'distance' => $result[0]->distance,
                'schema' => $schema,
                'vector_cast' => $vectorCast,
                'query_used' => $query,
                'database_info' => $this->getDatabaseInfo()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing pgvector: ' . $e->getMessage(),
                'database_info' => $this->getDatabaseInfo()
            ], 500);
        }
    }
    
    /**
     * Test a specific vector syntax
     */
    public function testSyntax(Request $request)
    {
        try {
            $syntax = $request->query('syntax', '::vector');
            $query = "SELECT '[1,2,3]'$syntax <=> '[4,5,6]'$syntax AS distance";
            $result = DB::connection('pgsql')->select($query);
            
            return response()->json([
                'success' => true,
                'message' => "Vector query successful with syntax: $syntax",
                'distance' => $result[0]->distance,
                'query_used' => $query
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Vector query failed with syntax: $syntax",
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check the PostgreSQL search path
     */
    public function checkSearchPath()
    {
        try {
            $result = DB::connection('pgsql')->select('SHOW search_path');

            return response()->json([
                'success' => true,
                'message' => 'Search path retrieved successfully',
                'search_path' => $result[0]->search_path,
                'database_info' => $this->getDatabaseInfo()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve search path',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * View a sample record from the embeddings table
     */
    public function viewEmbedding()
    {
        try {
            $result = DB::connection('pgsql')->select('SELECT id, content, metadata, embedding FROM embeddings LIMIT 1');

            if (empty($result)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No records found in embeddings table',
                    'table_exists' => $this->checkTableExists('embeddings')
                ]);
            }

            // Convert embedding to a more readable format if it exists
            if (isset($result[0]->embedding)) {
                // Try to determine if it's a binary format or already a string/array
                if (is_resource($result[0]->embedding)) {
                    $result[0]->embedding = stream_get_contents($result[0]->embedding);
                }

                // Add embedding info
                $embeddingInfo = [
                    'type' => gettype($result[0]->embedding),
                    'sample' => is_string($result[0]->embedding) ?
                        substr($result[0]->embedding, 0, 100) . '...' :
                        'Non-string embedding'
                ];
            } else {
                $embeddingInfo = ['type' => 'not_present'];
            }

            return response()->json([
                'success' => true,
                'message' => 'Retrieved sample embedding',
                'record' => $result[0],
                'embedding_info' => $embeddingInfo,
                'table_info' => $this->getTableInfo('embeddings')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve embedding sample',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get database information
     */
    private function getDatabaseInfo()
    {
        try {
            $version = DB::connection('pgsql')->select('SELECT version()');
            $database = DB::connection('pgsql')->select('SELECT current_database()');
            $extensions = DB::connection('pgsql')->select('SELECT e.extname as name, e.extnamespace as schema FROM pg_extension e');
            $connectionInfo = [
                'driver' => config('database.connections.pgsql.driver'),
                'host' => config('database.connections.pgsql.host'),
                'port' => config('database.connections.pgsql.port'),
                'database' => config('database.connections.pgsql.database'),
            ];
            
            return [
                'database' => $database[0]->current_database,
                'version' => $version[0]->version,
                'extensions' => $extensions,
                'connection_info' => $connectionInfo
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check if a table exists
     */
    private function checkTableExists($tableName)
    {
        try {
            $result = DB::connection('pgsql')
                ->select("SELECT to_regclass('public.$tableName') IS NOT NULL AS exists");
            return $result[0]->exists;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get table structure information
     */
    private function getTableInfo($tableName)
    {
        try {
            $columns = DB::connection('pgsql')
                ->select("SELECT column_name, data_type, udt_name
                          FROM information_schema.columns
                          WHERE table_name = ?
                           ORDER BY ordinal_position", [$tableName]);

            return [
                'exists' => true,
                'columns' => $columns
            ];
        } catch (\Exception $e) {
            return [
                'exists' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
```

### Test Routes

Add these routes to `routes/web.php`:

```php
// PGVector test routes
Route::get('/pgvector-test', [\App\Http\Controllers\PgvectorTestController::class, 'show']);
Route::prefix('api')->group(function () {
    Route::get('/test-pgvector', [\App\Http\Controllers\PgvectorTestController::class, 'test']);
    Route::get('/test-pgvector/syntax', [\App\Http\Controllers\PgvectorTestController::class, 'testSyntax']);
    Route::get('/test-pgvector/search-path', [\App\Http\Controllers\PgvectorTestController::class, 'checkSearchPath']);
    Route::get('/test-pgvector/embedding', [\App\Http\Controllers\PgvectorTestController::class, 'viewEmbedding']);
});
```

### Test View

Create `resources/views/pgvector-test.blade.php` with an interactive UI for testing:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PGVector Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-4">PGVector Test</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Test Vector Operations</h2>
            <div class="mb-4 flex space-x-4">
                <button id="testButton" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Run Test
                </button>
                <button id="searchPathButton" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded">
                    Check Search Path
                </button>
                <button id="viewEmbeddingButton" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                    View Sample Embedding
                </button>
            </div>
            
            <div id="loading" class="hidden">
                <p class="text-gray-600">Testing pgvector functionality...</p>
            </div>
            
            <div id="results" class="hidden">
                <h3 class="text-lg font-medium mb-2">Results:</h3>
                <pre id="resultJson" class="bg-gray-100 p-4 rounded overflow-auto max-h-96"></pre>
            </div>
            
            <div id="error" class="hidden">
                <h3 class="text-lg font-medium mb-2 text-red-600">Error:</h3>
                <pre id="errorJson" class="bg-red-50 p-4 rounded overflow-auto max-h-96"></pre>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Test Vector Syntax</h2>
            <div class="mb-4 flex flex-wrap gap-2">
                <button class="test-syntax bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded" data-syntax="::vector">
                    Test ::vector
                </button>
                <button class="test-syntax bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded" data-syntax="::public.vector">
                    Test ::public.vector
                </button>
                <button class="test-syntax bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded" data-syntax="::extensions.vector">
                    Test ::extensions.vector
                </button>
                <button class="test-syntax bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded" data-syntax="::pg_catalog.vector">
                    Test ::pg_catalog.vector
                </button>
                <button class="test-syntax bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded" data-syntax="">
                    Test No Cast
                </button>
            </div>
            <div class="mt-4">
                <input type="text" id="customSyntax" placeholder="Custom syntax (e.g., ::pgvector.vector)" 
                       class="border rounded px-3 py-2 w-64">
                <button id="testCustom" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded ml-2">
                    Test Custom
                </button>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Schema Information</h2>
            <div class="space-y-2">
                <p>Based on your search path results:</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Search path is set to: <code>public</code></li>
                    <li>Vector extension is installed in schema: <code>16388</code> (likely <code>extensions</code>)</li>
                </ul>
                <p class="mt-2">Try the different syntax options above to determine which one works with your Supabase configuration.</p>
                <p class="mt-2 text-sm text-gray-600">Note: The schema number 16388 typically corresponds to the <code>extensions</code> schema in Supabase.</p>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const testButton = document.getElementById('testButton');
            const searchPathButton = document.getElementById('searchPathButton');
            const viewEmbeddingButton = document.getElementById('viewEmbeddingButton');
            const loading = document.getElementById('loading');
            const results = document.getElementById('results');
            const resultJson = document.getElementById('resultJson');
            const error = document.getElementById('error');
            const errorJson = document.getElementById('errorJson');
            
            testButton.addEventListener('click', function() {
                runTest('/api/test-pgvector');
            });
            
            searchPathButton.addEventListener('click', function() {
                runTest('/api/test-pgvector/search-path');
            });
            
            viewEmbeddingButton.addEventListener('click', function() {
                runTest('/api/test-pgvector/embedding');
            });
            
            document.querySelectorAll('.test-syntax').forEach(button => {
                button.addEventListener('click', function() {
                    const syntax = this.getAttribute('data-syntax');
                    runTest(`/api/test-pgvector/syntax?syntax=${encodeURIComponent(syntax)}`);
                });
            });
            
            document.getElementById('testCustom').addEventListener('click', function() {
                const syntax = document.getElementById('customSyntax').value;
                if (syntax) {
                    runTest(`/api/test-pgvector/syntax?syntax=${encodeURIComponent(syntax)}`);
                }
            });
            
            function runTest(url) {
                loading.classList.remove('hidden');
                results.classList.add('hidden');
                error.classList.add('hidden');
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        loading.classList.add('hidden');
                        results.classList.remove('hidden');
                        resultJson.textContent = JSON.stringify(data, null, 2);
                        
                        if (!data.success) {
                            error.classList.remove('hidden');
                            errorJson.textContent = data.error || 'Unknown error';
                        }
                    })
                    .catch(err => {
                        loading.classList.add('hidden');
                        error.classList.remove('hidden');
                        errorJson.textContent = err.message;
                    });
            }
        });
    </script>
</body>
</html>
```

## Common Issues

### Supabase-Specific Issues

1. **Schema Mismatch**: Supabase often installs extensions in the `extensions` schema rather than `public`
2. **Search Path Configuration**: Default search path may not include the extensions schema
3. **Permission Issues**: Ensure your database user has proper permissions

### Laravel-Specific Issues

1. **Package Registration**: Ensure the pgvector package is properly registered
2. **Connection Configuration**: Check that the PostgreSQL connection is properly configured
3. **Query Builder Issues**: Be careful with Laravel's query builder when using custom PostgreSQL types

## Best Practices

1. **Dynamic Schema Detection**: Always detect the schema where pgvector is installed
2. **Fallback Mechanisms**: Implement fallbacks for different casting syntaxes
3. **Error Handling**: Add detailed error handling for vector operations
4. **Testing Interface**: Use the provided testing interface to diagnose issues

## Conclusion

By following this guide, you should be able to successfully integrate pgvector with Laravel, even when using Supabase or other PostgreSQL hosting services. The key is properly detecting the schema where pgvector is installed and using the correct vector casting syntax in your queries.

If you encounter persistent issues, use the testing interface to diagnose the problem and determine the correct vector casting syntax for your specific PostgreSQL configuration.
