<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Aws\Sdk;
use Aws\Credentials\Credentials;
use Aws\BedrockRuntime\BedrockRuntimeClient;

class EmbeddingService
{
    protected string $docsPath = 'docs';
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

        // Log the configuration details for debugging
        Log::info('EmbeddingService initialized with the following configuration:');
        Log::info('Embedding Model ID: ' . $this->embeddingModelId);
        Log::info('Region: ' . $this->region);

        // Initialize AWS SDK client for Bedrock
        if (empty($this->accessKey) || empty($this->secretKey)) {
            Log::warning('AWS credentials not found. Embeddings will not work properly.');
        } else {
            $sdk = new Sdk([
                'region' => $this->region,
                'version' => 'latest',
                'credentials' => new Credentials($this->accessKey, $this->secretKey)
            ]);

            $this->client = $sdk->createBedrockRuntime();
        }
    }

    /**
     * Check if PostgreSQL driver is available
     *
     * @return bool
     */
    protected function isPgsqlDriverAvailable(): bool
    {
        return extension_loaded('pdo_pgsql');
    }

    /**
     * Safely execute a database operation, handling driver errors
     *
     * @param callable $callback The database operation to execute
     * @return mixed|null The result of the callback or null if it fails
     */
    protected function safeDbOperation(callable $callback)
    {
        if (!$this->isPgsqlDriverAvailable()) {
            Log::error('PostgreSQL driver not found. Please install the pdo_pgsql PHP extension.');
            return null;
        }

        try {
            return $callback();
        } catch (\Exception $e) {
            Log::error('Database operation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find all markdown files recursively in a directory
     *
     * @param string $dir Directory to search in
     * @return array Array of file paths
     */
    protected function findMarkdownFiles(string $dir): array
    {
        $result = [];
        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                // Recursively search subdirectories
                $result = array_merge($result, $this->findMarkdownFiles($path));
            } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'md') {
                // Add markdown files to result
                $result[] = $path;
            }
        }

        return $result;
    }

    public function run($clearExisting = true, $sourceType = 'help_document')
    {
        if (!$this->isPgsqlDriverAvailable()) {
            Log::error('PostgreSQL driver not found. Please install the pdo_pgsql PHP extension.');
            Log::error('On Windows: Enable extension=pdo_pgsql in your php.ini file and restart your web server.');
            Log::error('On Linux: Install php-pgsql package (e.g., sudo apt-get install php-pgsql) and restart your web server.');
            return;
        }

        // Get all markdown files recursively from the docs directory
        $docsBasePath = base_path($this->docsPath);
        $files = $this->findMarkdownFiles($docsBasePath);

        Log::info("Found " . count($files) . " markdown files in {$this->docsPath} and its subdirectories");

        if ($clearExisting) {
            // Option to clear all existing embeddings before processing
            Log::info("Clearing all existing embeddings of type: {$sourceType}");
            $this->safeDbOperation(function() use ($sourceType) {
                return DB::connection('pgsql')->table('embeddings')
                    ->where('metadata->source_type', $sourceType)
                    ->delete();
            });
        }

        foreach ($files as $file) {
            $fileName = basename($file);
            $relativePath = str_replace(base_path(), '', $file);

           // Option to clear just embeddings for this specific file
            if (!$clearExisting) {
                Log::info("Clearing existing embeddings for file: {$fileName}");
                $this->safeDbOperation(function() use ($fileName) {
                    return DB::connection('pgsql')->table('embeddings')
                        ->where('metadata->source', $fileName)
                        ->delete();
                });
            }

            $content = file_get_contents($file);
            $chunks = $this->chunkContent($content);

            Log::info("Processing file {$fileName}: found " . count($chunks) . " chunks");

            foreach ($chunks as $chunk) {
                $embedding = $this->getEmbedding($chunk);

                // Enhanced metadata
                $metadata = [
                    'source' => $fileName,
                    'source_type' => $sourceType,
                    'path' => $relativePath,
                    'last_updated' => filemtime($file),
                    'chunk_length' => strlen($chunk),
                ];

                $this->insertEmbedding($chunk, $embedding, $metadata);

            }
        }

        Log::info("Embedding generation completed for source type: {$sourceType}");
    }

    /**
     * Track token counts: Since the average English token is around
     * 4.7 characters, 8,192 tokens equates to ~38,500 characters
     * (though AWS notes you can go up to ~50,000 characters total)
     */
    /**
     * Chunk content into segments of approximately 200 words or 1000 characters, whichever comes first
     *
     * @param string $text The text to chunk
     * @param int $wordCount The target number of words per chunk
     * @param int $charCount The maximum number of characters per chunk
     * @return array The chunked content
     */
    protected function chunkContent(string $text, int $wordCount = 200, int $charCount = 1000): array
    {
        $paragraphs = explode("\n\n", $text);
        $chunks = [];
        $buffer = '';
        $currentWordCount = 0;

        foreach ($paragraphs as $para) {
            // Count words and characters in this paragraph
            $paraWordCount = str_word_count($para);
            $paraCharCount = Str::length($para) + 2; // +2 for the newlines
            
            // Check if adding this paragraph would exceed either limit
            $exceedsWordLimit = ($currentWordCount + $paraWordCount > $wordCount) && ($currentWordCount > 0);
            $exceedsCharLimit = (Str::length($buffer) + $paraCharCount > $charCount) && (!empty($buffer));
            
            if ($exceedsWordLimit || $exceedsCharLimit) {
                // Save the current buffer as a chunk
                $chunks[] = trim($buffer);
                $buffer = $para . "\n\n";
                $currentWordCount = $paraWordCount;
            } else {
                // Add this paragraph to the buffer
                $buffer .= $para . "\n\n";
                $currentWordCount += $paraWordCount;
            }
        }

        // Don't forget the last chunk if there's anything left in the buffer
        if (!empty(trim($buffer))) {
            $chunks[] = trim($buffer);
        }

        return $chunks;
    }

    protected function getEmbedding(string $text): array
    {
        try {
            if (!$this->client) {
                Log::error('Bedrock client not initialized. Cannot generate embeddings.');
                return [];
            }

            // Prepare the payload for the Titan Embeddings model
            $payload = json_encode([
                'inputText' => $text
            ]);

            // Log the request details
           // Log::info('Making Bedrock embedding API call with the following parameters:');
           // Log::info('Model ID: ' . $this->embeddingModelId);
           // Log::info('Text length: ' . strlen($text));

            // Call the Bedrock API
            $response = $this->client->invokeModel([
                'modelId' => $this->embeddingModelId,
                'contentType' => 'application/json',
                'accept' => 'application/json',
                'body' => $payload
            ]);

            // Parse the response
            $result = json_decode($response['body']->getContents(), true);

            // The Titan embedding model returns the embedding in the 'embedding' field
            return $result['embedding'];

        } catch (\Exception $e) {
            Log::error('Error generating embedding: ' . $e->getMessage());
            return [];
        }
    }

    protected function insertEmbedding(string $chunk, array $vector, array $metadata)
    {
        $this->safeDbOperation(function() use ($chunk, $vector, $metadata) {
            return DB::connection('pgsql')->insert("
                INSERT INTO embeddings (content, embedding, metadata)
                VALUES (?, ?, ?)",
                [
                    $chunk,
                    '[' . implode(',', $vector) . ']',
                    json_encode($metadata)
                ]
            );
        });
    }
}
