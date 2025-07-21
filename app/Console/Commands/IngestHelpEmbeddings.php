<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmbeddingService;

class IngestHelpEmbeddings extends Command
{
    protected $signature = 'embedding:ingest-help {--clear-all : Clear all embeddings of the specified type before processing} {--type=help_document : Type of document being processed}';
    protected $description = 'Ingest help file content and store embeddings in Supabase';

    public function handle(EmbeddingService $service)
    {
        $clearAll = $this->option('clear-all');
        $type = $this->option('type');
        if(!$type){
            $this->error('--type , Type parameter is required');
            return;
        }

        $this->info("Processing {$type} documents" . ($clearAll ? ' (clearing all existing embeddings first)' : ''));

        $service->run($clearAll, $type);

        $this->info("{$type} embeddings inserted successfully.");
    }
}
