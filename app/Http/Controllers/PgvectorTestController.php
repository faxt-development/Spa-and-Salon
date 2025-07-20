<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PgvectorTestController extends Controller
{
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
                    'suggestion' => 'You need to enable the vector extension in your Supabase database',
                    'database_info' => $this->getDatabaseInfo()
                ], 500);
            }

            // Check which schema the vector extension is in
            $schemaCheck = DB::connection('pgsql')
                ->select("SELECT n.nspname as schema_name FROM pg_extension e JOIN pg_namespace n ON e.extnamespace = n.oid WHERE e.extname = 'vector'");

            $schema = $schemaCheck[0]->schema_name ?? 'public';

            // Test with public schema
            try {
                $publicResult = $this->testVectorQuery('::vector');
                return response()->json([
                    'success' => true,
                    'message' => 'pgvector is working correctly with public schema',
                    'schema' => $schema,
                    'distance' => $publicResult[0]->distance,
                    'query_used' => "SELECT '[1,2,3]'::vector <=> '[4,5,6]'::vector AS distance",
                    'database_info' => $this->getDatabaseInfo()
                ]);
            } catch (\Exception $e) {
                // Try with schema-qualified syntax
                try {
                    $schemaResult = $this->testVectorQuery("::$schema.vector");
                    return response()->json([
                        'success' => true,
                        'message' => "pgvector is working correctly with $schema schema",
                        'schema' => $schema,
                        'distance' => $schemaResult[0]->distance,
                        'query_used' => "SELECT '[1,2,3]'::$schema.vector <=> '[4,5,6]'::$schema.vector AS distance",
                        'database_info' => $this->getDatabaseInfo()
                    ]);
                } catch (\Exception $e2) {
                    // Try with extensions schema as a last resort
                    try {
                        $extensionsResult = $this->testVectorQuery('::extensions.vector');
                        return response()->json([
                            'success' => true,
                            'message' => 'pgvector is working correctly with extensions schema',
                            'schema' => 'extensions',
                            'distance' => $extensionsResult[0]->distance,
                            'query_used' => "SELECT '[1,2,3]'::extensions.vector <=> '[4,5,6]'::extensions.vector AS distance",
                            'database_info' => $this->getDatabaseInfo()
                        ]);
                    } catch (\Exception $e3) {
                        return response()->json([
                            'success' => false,
                            'message' => 'pgvector is not working correctly',
                            'public_error' => $e->getMessage(),
                            'schema_error' => $e2->getMessage(),
                            'extensions_error' => $e3->getMessage(),
                            'database_info' => $this->getDatabaseInfo()
                        ], 500);
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking pgvector extension',
                'error' => $e->getMessage(),
                'database_info' => $this->getDatabaseInfo()
            ], 500);
        }
    }

    /**
     * Get database information for debugging
     */
    private function getDatabaseInfo()
    {
        try {
            $connection = DB::connection('pgsql');
            $database = $connection->getDatabaseName();
            $version = $connection->select('SELECT version()');
            $extensions = $connection->select('SELECT * FROM pg_extension');

            $extensionsList = [];
            foreach ($extensions as $extension) {
                $extensionsList[] = [
                    'name' => $extension->extname,
                    'schema' => $extension->extnamespace,
                ];
            }

            return [
                'database' => $database,
                'version' => $version[0]->version ?? 'unknown',
                'extensions' => $extensionsList,
                'connection' => config('database.connections.pgsql')
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Could not get database info: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test a vector query with specific casting syntax
     */
    private function testVectorQuery($vectorCast)
    {
        $query = "SELECT '[1,2,3]'$vectorCast <=> '[4,5,6]'$vectorCast AS distance";
        return DB::connection('pgsql')->select($query);
    }

    /**
     * Show the pgvector test view
     */
    public function show()
    {
        return view('pgvector-test');
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
     * Test vector operations using direct schema ID casting
     */
    public function testSchemaId()
    {
        try {
            // Get the actual schema OID for the vector extension
            $schemaInfo = DB::connection('pgsql')
                ->select("SELECT e.extnamespace as schema_oid FROM pg_extension e WHERE e.extname = 'vector'");

            if (empty($schemaInfo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vector extension not found in pg_extension table',
                    'database_info' => $this->getDatabaseInfo()
                ]);
            }

            $schemaOid = $schemaInfo[0]->schema_oid;

            // Try using the schema OID directly
            $query = "SELECT '[1,2,3]'::$schemaOid.vector <=> '[4,5,6]'::$schemaOid.vector AS distance";

            try {
                $result = DB::connection('pgsql')->select($query);

                return response()->json([
                    'success' => true,
                    'message' => "Vector query successful using schema OID: $schemaOid",
                    'distance' => $result[0]->distance,
                    'query_used' => $query,
                    'schema_oid' => $schemaOid
                ]);
            } catch (\Exception $e) {
                // Try alternative approach with schema name lookup
                $schemaNameInfo = DB::connection('pgsql')
                    ->select("SELECT nspname FROM pg_namespace WHERE oid = $schemaOid");

                if (empty($schemaNameInfo)) {
                    throw new \Exception("Could not find schema name for OID: $schemaOid");
                }

                $schemaName = $schemaNameInfo[0]->nspname;
                $query2 = "SELECT '[1,2,3]'::$schemaName.vector <=> '[4,5,6]'::$schemaName.vector AS distance";
                $result2 = DB::connection('pgsql')->select($query2);

                return response()->json([
                    'success' => true,
                    'message' => "Vector query successful using schema name: $schemaName",
                    'distance' => $result2[0]->distance,
                    'query_used' => $query2,
                    'schema_oid' => $schemaOid,
                    'schema_name' => $schemaName
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing vector with schema ID',
                'error' => $e->getMessage(),
                'database_info' => $this->getDatabaseInfo()
            ], 500);
        }
    }

    /**
     * Test raw SQL vector operations
     */
    public function testRawSql(Request $request)
    {
        try {
            $sql = $request->query('sql', "SELECT '[1,2,3]'::extensions.vector <=> '[4,5,6]'::extensions.vector AS distance");
            $result = DB::connection('pgsql')->select($sql);

            return response()->json([
                'success' => true,
                'message' => 'Raw SQL query executed successfully',
                'result' => $result,
                'sql' => $sql
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Raw SQL query failed',
                'error' => $e->getMessage(),
                'sql' => $request->query('sql')
            ], 500);
        }
    }

    /**
     * Test the RPC approach for vector similarity search
     */
    public function testRpcApproach(Request $request)
    {
        try {
            // Step 1: Check if the match_embeddings function exists, create if not
            $functionCheck = DB::connection('pgsql')
                ->select("SELECT 1 FROM pg_proc WHERE proname = 'match_embeddings'");

            if (empty($functionCheck)) {
                // Create the function for vector similarity search
                DB::connection('pgsql')->statement("
                    CREATE OR REPLACE FUNCTION match_embeddings (
                      query_embedding vector(1024),
                      match_threshold float,
                      match_count int,
                      source_type text DEFAULT NULL
                    )
                    RETURNS TABLE (
                      id bigint,
                      content text,
                      metadata jsonb,
                      similarity float
                    )
                    LANGUAGE sql STABLE
                    AS $$
                      SELECT
                        embeddings.id,
                        embeddings.content,
                        embeddings.metadata,
                        1 - (embeddings.embedding <=> query_embedding) AS similarity
                      FROM embeddings
                      WHERE
                        (source_type IS NULL OR metadata->>'source_type' = source_type)
                        AND 1 - (embeddings.embedding <=> query_embedding) > match_threshold
                      ORDER BY embeddings.embedding <=> query_embedding ASC
                      LIMIT match_count;
                    $$;
                ");

                return response()->json([
                    'status' => 'success',
                    'message' => 'Created match_embeddings function',
                    'step' => 'function_creation'
                ]);
            }

            // Step 2: Test the function with a sample embedding
            // Use a simple 3-dimensional vector for testing
            $sampleEmbedding = '[0.1, 0.2, 0.3]';

            $testQuery = "
                SELECT * FROM match_embeddings(
                    '$sampleEmbedding',
                    0.0,
                    5,
                    'help_document'
                )
            ";

            $results = DB::connection('pgsql')->select($testQuery);

            return response()->json([
                'status' => 'success',
                'message' => 'RPC approach test successful',
                'results' => $results,
                'count' => count($results)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error testing RPC approach',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if vector extension is properly registered
     */
    public function checkExtension()
    {
        try {
            // Check for vector extension in pg_extension
            $extensionCheck = DB::connection('pgsql')
                ->select("SELECT * FROM pg_extension WHERE extname = 'vector'");

            // Check for vector type in pg_type
            $typeCheck = DB::connection('pgsql')
                ->select("SELECT * FROM pg_type WHERE typname = 'vector'");

            // Check for vector operators in pg_operator
            $operatorCheck = DB::connection('pgsql')
                ->select("SELECT * FROM pg_operator WHERE oprname = '<=>' LIMIT 5");

            // Check if we can create a vector directly
            $createTest = null;
            $createError = null;
            try {
                $createTest = DB::connection('pgsql')
                    ->select("SELECT '[1,2,3]' AS raw_vector, vector_dims('[1,2,3]'::vector) AS dimensions");
            } catch (\Exception $e) {
                $createError = $e->getMessage();
            }

            return response()->json([
                'success' => true,
                'extension_exists' => !empty($extensionCheck),
                'extension_info' => $extensionCheck,
                'type_exists' => !empty($typeCheck),
                'type_info' => $typeCheck,
                'operator_exists' => !empty($operatorCheck),
                'operator_count' => count($operatorCheck),
                'can_create_vector' => $createTest !== null,
                'create_test' => $createTest,
                'create_error' => $createError,
                'database_info' => $this->getDatabaseInfo()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking vector extension',
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
