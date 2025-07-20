# Faxtina Assistant UI

## Overview

The Faxtina Assistant UI is a smart help system that provides contextual assistance to users by searching through documentation using natural language queries. It leverages vector embeddings stored in a PostgreSQL database to find relevant content from the `/docs` folder.

## Features

- Floating assistant panel accessible from any page in the dashboard
- Natural language query support
- Vector similarity search for relevant documentation
- Source metadata display
- Loading states and error handling
- Suggested questions to help users get started

## Technical Implementation

### Components

1. **Blade Component**: `resources/views/components/assistant.blade.php`
   - Provides the UI for the assistant panel
   - Uses Alpine.js for reactivity and AJAX requests
   - Handles loading states and result display

2. **Controller**: `app/Http/Controllers/AssistantController.php`
   - Generates embeddings for user queries using AWS Bedrock
   - Searches the PostgreSQL database for similar content
   - Returns formatted results with content and metadata

3. **Route**: Added in `routes/web.php`
   ```php
   Route::post('/assistant/search', [AssistantController::class, 'search'])
       ->middleware(['auth:web'])
       ->name('assistant.search');
   ```

4. **Integration**: Added to `resources/views/layouts/app.blade.php`
   ```php
   <!-- Assistant Component -->
   <x-assistant />
   ```

### Database Structure

The assistant uses the `embeddings` table in PostgreSQL with the following structure:

```sql
CREATE TABLE embeddings (
    id SERIAL PRIMARY KEY,
    content TEXT NOT NULL,
    embedding VECTOR(1024) NOT NULL,  -- Dimension for Titan Embeddings v2 model
    metadata JSONB,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
```

The metadata field contains:
- `source`: The filename
- `source_type`: Type of document (e.g., 'help_document', 'faq')
- `path`: Relative file path
- `last_updated`: File modification timestamp
- `chunk_length`: Length of the text chunk

### How It Works

1. When a user clicks the "Ask Assistant" button, the panel slides up
2. The user types a natural language question and submits it
3. The query is sent to the backend where it's converted to an embedding vector using AWS Bedrock
4. The database searches for similar content using vector similarity (`<=>` operator)
5. Top matching results are returned with their source metadata
6. Results are displayed in the panel with the source document indicated

### AWS Bedrock Configuration

The assistant uses Amazon's Titan Embeddings model for generating embeddings:

```php
$this->embeddingModelId = config('services.aws.bedrock.embedding_model_id', 'amazon.titan-embed-text-v2:0');
```

Make sure your AWS credentials are properly configured in your `.env` file:

```
AWS_ACCESS_KEY_ID=your_aws_access_key
AWS_SECRET_ACCESS_KEY=your_aws_secret_key
AWS_DEFAULT_REGION=us-east-1
```

## Usage

### For Users

1. Click the "Ask Assistant" button in the bottom-right corner of any page
2. Type your question in natural language (e.g., "How do I create a new appointment?")
3. View the most relevant documentation snippets that answer your question
4. Click on suggested questions to quickly get answers to common questions

### For Developers

To add new content to the assistant:

1. Add markdown files to the `/docs` folder
2. Run the embedding generation command:
   ```bash
   php artisan embedding:ingest-help
   ```

To customize the assistant:

1. Modify the Blade component in `resources/views/components/assistant.blade.php`
2. Adjust the search logic in `app/Http/Controllers/AssistantController.php`

## Future Enhancements

- Add more detailed source metadata display (file path, last updated)
- Implement search scope filters (e.g., only search certain document types)
- Add a typing effect or streaming response for a more natural feel
- Upgrade to a full RAG pipeline with GPT-style completions using retrieved chunks as context
- Add user feedback mechanism to improve search results over time
- Implement search history and saved questions
