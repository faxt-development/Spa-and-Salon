# AWS Bedrock Integration

## 1. Embeddings from Markdown Documentation

This feature allows users to automatically generate chunks of text from markdown documentation files, generate embeddings using AWS Bedrock, and store them in a PostgreSQL database for semantic search capabilities.

### Setup Instructions

1. Add the following environment variables to your `.env` file:

```
# AWS Bedrock Configuration
AWS_ACCESS_KEY_ID=your_aws_access_key
AWS_SECRET_ACCESS_KEY=your_aws_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BEDROCK_MODEL_ID=amazon.titan-embed-text-v2:0
```

2. Make sure you have the AWS SDK for PHP installed:

```
composer require aws/aws-sdk-php
```

3. Ensure your AWS account has access to Amazon Bedrock and the Titan Embeddings model.

### Usage

When creating or editing a site, click the "AI Generate" button to automatically generate:
- A main color based on the site name and description
- A logo image
- A favicon

The generated assets will automatically populate the form fields.

## 2. EmbeddingService for Documentation

The `EmbeddingService` class processes markdown documentation files, generates embeddings using AWS Bedrock, and stores them in a PostgreSQL database for semantic search capabilities.

### Overview

The `EmbeddingService` is responsible for:

1. Reading markdown files from the `resources/docs` directory
2. Chunking the content into manageable segments
3. Generating embeddings for each chunk using AWS Bedrock's Titan Embeddings model
4. Storing the chunks and their embeddings in the database

### Configuration

#### AWS Credentials

The service uses the same AWS credentials as the site asset generation feature:

```
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
```

These values are accessed through the Laravel configuration system:

```php
$this->region = config('services.aws.region', 'us-east-1');
$this->accessKey = config('services.aws.key');
$this->secretKey = config('services.aws.secret');
```

#### AWS Bedrock Model

The service uses Amazon's Titan Embeddings model by default:

```php
$this->embeddingModelId = config('services.aws.bedrock.embedding_model_id', 'amazon.titan-embed-text-v2:0');
```

You can override this in your `config/services.php` file:

```php
'aws' => [
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'bedrock' => [
        'embedding_model_id' => env('AWS_BEDROCK_EMBEDDING_MODEL', 'amazon.titan-embed-text-v2:0'),
    ],
],
```

### Database Configuration

The service stores embeddings in a PostgreSQL database table named `embeddings`. The table should have the following structure:

```sql
CREATE TABLE embeddings (
    id SERIAL PRIMARY KEY,
    content TEXT NOT NULL,
    embedding VECTOR(1024) NOT NULL,  -- Dimension for Titan Embeddings v2 model
    metadata JSONB,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
```

Make sure your PostgreSQL connection is properly configured in your `.env` file.

### Usage

#### Using the Laravel Command

The easiest way to process all markdown files and generate embeddings is to use the provided Laravel command:

```bash
php artisan embedding:ingest-help
```

This command will process all markdown files in the `docs` directory, generate embeddings, and store them in the database. By default, it will clear existing embeddings for each file before processing it to avoid duplicates.

#### Using the Service Directly

You can also use the `EmbeddingService` directly in your code:

```php
$embeddingService = new \App\Services\EmbeddingService();

// Process files with default settings (clears existing embeddings per file)
$embeddingService->run();

// Or clear all embeddings of a specific type before processing
$embeddingService->run(true, 'help_document');

// Process a different type of document
$embeddingService->run(true, 'faq');
```

This will:
1. Find all `.md` files in the `docs` directory
2. Process each file, chunking its content
3. Generate embeddings for each chunk
4. Store the chunks and embeddings in the database

#### Duplicate Prevention

The service includes two strategies to prevent duplicates:

1. **Per-file clearing**: By default, the service clears existing embeddings for each file before processing it
2. **Bulk clearing**: Optionally, you can clear all embeddings of a specific type before processing

This ensures you don't get duplicate entries when reprocessing files or adding new ones.

### Implementation Details

#### Content Chunking

The service chunks content by paragraphs and then further splits long paragraphs to ensure each chunk is manageable for the embedding model:

```php
protected function chunkContent(string $text, int $length = 500): array
{
    return collect(Str::of($text)->explode("\n\n"))
        ->flatMap(fn($para) => Str::of($para)->split("/(?<=.{".$length."})/"))
        ->filter()
        ->toArray();
}
```

#### Embedding Generation

Embeddings are generated using AWS Bedrock's Titan Embeddings model:

```php
protected function getEmbedding(string $text): array
{
    // Call the Bedrock API with the text
    // Parse and return the embedding vector
}
```

#### Database Storage

Embeddings are stored in the database along with the original content and enhanced metadata:

```php
protected function insertEmbedding(string $chunk, array $vector, array $metadata)
{
    DB::connection('pgsql')->insert("
        INSERT INTO embeddings (content, embedding, metadata)
        VALUES (?, ?, ?)",
        [
            $chunk,
            '[' . implode(',', $vector) . ']',
            json_encode($metadata)
        ]
    );
}
```

#### Enhanced Metadata

The service now stores rich metadata about each embedding:

```php
$metadata = [
    'source' => $fileName,           // The filename
    'source_type' => $sourceType,     // Type of document (e.g., 'help_document', 'faq')
    'path' => $relativePath,          // Relative file path
    'last_updated' => filemtime($file), // File modification timestamp
    'chunk_length' => strlen($chunk), // Length of the text chunk
];
```

This metadata enables:
- Tracking different types of data sources
- Filtering embeddings by source type or file
- Implementing selective updates based on source type or file path
- Tracking when each embedding was last updated

### Error Handling

The service includes error handling and logging:

- If AWS credentials are missing, a warning is logged
- If the Bedrock client fails to initialize, an error is logged
- If embedding generation fails, the error is logged and an empty array is returned


### I have requested access to amazon titan embedding v2. but how do I know what the model id is to use in the api call

The model ID for Amazon Titan Embedding V2 in Amazon Bedrock is:

amazon.titan-embed-text-v2:0

You'll use this model ID when making API calls to generate embeddings. Here's how you would use it in different contexts:

AWS CLI example:
aws bedrock-runtime invoke-model \
--model-id amazon.titan-embed-text-v2:0 \
--body '{"inputText": "Your text here", "dimensions": 1024}' \
--cli-binary-format raw-in-base64-out \
output.json \
--profile your-profile-name

AWS SDK example (Python):
import boto3

bedrock_runtime = boto3.client('bedrock-runtime')

response = bedrock_runtime.invoke_model(
    modelId='amazon.titan-embed-text-v2:0',
    body=json.dumps({
        'inputText': 'Your text here',
        'dimensions': 1024
    })
)

Key features of this embedding model:

Supports over 100 languages
Allows you to specify different embedding dimensions: 1024, 512, or 256
Can generate both floating-point and binary embeddings
Optimized for retrieval performance and accuracy
Make sure you've been granted access to this model in your AWS account before attempting to use it. You can verify this in the Amazon Bedrock console under "Model access."

Sources
Invoke Amazon Titan Text models on Amazon Bedrock using the Invoke Model API - Amazon Bedrock 

