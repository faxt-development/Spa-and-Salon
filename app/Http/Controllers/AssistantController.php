<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Aws\Sdk;
use Aws\Credentials\Credentials;
use Aws\BedrockRuntime\BedrockRuntimeClient;

class AssistantController extends Controller
{
    protected $client;
    protected $embeddingModelId;
    protected $region;
    protected $accessKey;
    protected $secretKey;

    public function __construct()
    {
        // Initialize embedding model (Amazon Titan Embeddings G1 - Text)
        $this->embeddingModelId = config('services.aws.bedrock.embedding_model_id', 'amazon.titan-embed-text-v2:0');

        $this->region = config('services.aws.region', 'us-east-1');
        $this->accessKey = config('services.aws.key');
        $this->secretKey = config('services.aws.secret');

        // Initialize AWS SDK client for Bedrock
        if (!empty($this->accessKey) && !empty($this->secretKey)) {
            $sdk = new Sdk([
                'region' => $this->region,
                'version' => 'latest',
                'credentials' => new Credentials($this->accessKey, $this->secretKey)
            ]);

            $this->client = $sdk->createBedrockRuntime();
        } else {
            Log::warning('AWS credentials not found. Assistant search will not work properly.');
        }
    }

    /**
     * Search for relevant content based on user query
     */
    /**
     * Pre-process the query to extract key concepts and improve search relevance
     *
     * @param string $query The original user query
     * @return string The enhanced query for embedding generation
     */
    protected function preprocessQuery(string $query): string
    {
        // Convert to lowercase for consistent matching
        $query = strtolower($query);

        // Extract service-related keywords
        if (preg_match('/(add|create|make|new)\s+(a\s+)?(service|services)/i', $query)) {
            return $query . ' service management create new service';
        }

        // Extract editing-related keywords
        if (preg_match('/(edit|update|modify|change)\s+(a\s+)?(service|services)/i', $query)) {
            return $query . ' service management edit update service';
        }

        // Extract deletion-related keywords
        if (preg_match('/(delete|remove|disable)\s+(a\s+)?(service|services)/i', $query)) {
            return $query . ' service management delete remove service';
        }

        // Extract template-related keywords
        if (preg_match('/(template|templates)\s+(service|services)/i', $query)) {
            return $query . ' template services management';
        }

        // Default case - return original query
        return $query;
    }

    public function search(Request $request)
    {
        $originalQuery = $request->input('query');
        $query = $this->preprocessQuery($originalQuery);

        Log::info('Assistant search query received: ' . $originalQuery);
        Log::info('Preprocessed query: ' . $query);

        if (empty($query)) {
            Log::info('Empty query received, returning empty results');
            return response()->json([]);
        }

        try {
   /*         // Check if we have any embeddings at all
            $embeddingCount = DB::connection('pgsql')->select("SELECT COUNT(*) as count FROM embeddings");
            Log::info('Total embeddings in database: ' . $embeddingCount[0]->count);

            // List available document sources
            $sources = DB::connection('pgsql')->select("SELECT DISTINCT metadata->>'source' as source FROM embeddings");
            Log::info('Available document sources: ' . json_encode(array_column($sources, 'source')));
*/
            // Generate embedding for the query
            Log::info('Generating embedding for query: ' . $query);
            $embedding = $this->getEmbedding($query);

            if (empty($embedding)) {
                Log::error('Failed to generate embedding for query: ' . $query);
                return response()->json([
                    ['content' => 'Sorry, I could not process your question. Please try again later.']
                ], 500);
            }

            Log::info('Successfully generated embedding vector with ' . count($embedding) . ' dimensions');

            // Now that we've added the extensions schema to the search path,
            // we can use direct vector casting without schema qualification

            // Convert embedding array to string format for the query
            $embeddingStr = '[' . implode(',', $embedding) . ']';
            $vectorSql = "'$embeddingStr'::vector";

            Log::info('Using direct vector casting with updated search path');

            // Search for similar content in the database
            // First try with a higher similarity threshold
            $sqlQuery = "
                SELECT content, metadata, 1 - (embedding <=> $vectorSql) AS similarity
                FROM embeddings
                WHERE metadata->>'source_type' = 'help_document'
                  AND (1 - (embedding <=> $vectorSql)) > 0.7
                ORDER BY embedding <=> $vectorSql ASC
                LIMIT 5
            ";

            // Fallback query without threshold
            $fallbackQuery = "
                SELECT content, metadata, 1 - (embedding <=> $vectorSql) AS similarity
                FROM embeddings
                WHERE metadata->>'source_type' = 'help_document'
                ORDER BY embedding <=> $vectorSql ASC
                LIMIT 5
            ";

            // Define an RPC query as a last resort
            $rpcQuery = $sqlQuery; // Same query but will be executed differently if needed
            
            // Try standard SQL query first
            Log::info('Searching for similar content using standard SQL query');
            $results = [];
            $runFallback = false;
            $runRpc = false;
            
            try {
                $results = DB::connection('pgsql')->select($sqlQuery);
                Log::info('Standard SQL query returned ' . count($results) . ' results');
                
                // If no results with high threshold, try the fallback query
                if (empty($results)) {
                    $runFallback = true;
                }
            } catch (\Throwable $th) {
                Log::error('Error executing standard SQL query: ' . $th->getMessage());
                $runFallback = true;
            }
            
            // Try fallback query if needed
            if ($runFallback) {
                try {
                    Log::info('Using fallback query without threshold');
                    $results = DB::connection('pgsql')->select($fallbackQuery);
                    Log::info('Fallback query returned ' . count($results) . ' results');
                    
                    // If still no results, try RPC approach
                    if (empty($results)) {
                        $runRpc = true;
                    }
                } catch (\Throwable $th) {
                    Log::error('Error executing fallback query: ' . $th->getMessage());
                    $runRpc = true;
                }
            }
            
            // Try RPC approach as last resort
            if ($runRpc) {
                try {
                    Log::info('Using RPC approach as last resort');
                    $results = DB::connection('pgsql')->select($rpcQuery);
                    Log::info('RPC query returned ' . count($results) . ' results');
                } catch (\Throwable $th) {
                    Log::error('Error executing RPC query: ' . $th->getMessage());
                    // All queries failed, return empty results
                    $results = [];
                }
            }

            // Process results to include metadata
            $processedResults = [];
            foreach ($results as $result) {
                $metadata = json_decode($result->metadata);
                $similarity = $result->similarity;

                Log::info('Result found with similarity: ' . $similarity);
                Log::info('From source: ' . ($metadata->source ?? 'unknown'));
                Log::info('Content snippet: ' . substr($result->content, 0, 100) . '...');

                $processedResults[] = [
                    'content' => $result->content,
                    'metadata' => $metadata,
                    'similarity' => $similarity
                ];
            }

            return response()->json($processedResults);

        } catch (\Exception $e) {
            Log::error('Error in assistant search: ' . $e->getMessage());
            return response()->json([
                ['content' => 'Sorry, an error occurred while processing your question. Please try again later.']
            ], 500);
        }
    }

    /**
     * Generate embedding for a text using AWS Bedrock
     */
    protected function getEmbedding(string $text): array
    {
        try {
            if (!$this->client) {
                Log::error('Bedrock client not initialized. Cannot generate embeddings.');
                return [];
            }

            Log::info('getEmbedding: Preparing payload for text of length ' . strlen($text));
            Log::info('getEmbedding: Text snippet: ' . substr($text, 0, 100) . '...');

            // Prepare the payload for the Titan Embeddings model
            $payload = json_encode([
                'inputText' => $text
            ]);

            Log::info('getEmbedding: Calling AWS Bedrock API with model ID: ' . $this->embeddingModelId);

            // Call the Bedrock API
            $response = $this->client->invokeModel([
                'modelId' => $this->embeddingModelId,
                'contentType' => 'application/json',
                'accept' => 'application/json',
                'body' => $payload
            ]);

            Log::info('getEmbedding: Received response from AWS Bedrock API');

            // Parse the response
            $result = json_decode($response['body']->getContents(), true);

            if (!isset($result['embedding'])) {
                Log::error('getEmbedding: Embedding field missing from response: ' . json_encode($result));
                return [];
            }

            Log::info('getEmbedding: Successfully extracted embedding vector with ' . count($result['embedding']) . ' dimensions');

            // The Titan embedding model returns the embedding in the 'embedding' field
            return $result['embedding'];

        } catch (\Exception $e) {
            Log::error('Error generating embedding: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());
            return [];
        }
    }
}
