<?php
namespace App\Chain;

use Illuminate\Support\Facades\Log;

class LogImportHandler
{
    /**
     * Handle the payload and return it for the next handler
     */
    public function handle(array $payload): array
    {
        $rows = $payload['rows'];
        $fileName = $payload['fileName'] ?? 'unknown';

        // Log import info
        Log::info("Bulk import pipeline completed. Total rows: " . $rows->count() . " | File: {$fileName}");

        // Return payload for next handler in the pipeline
        return $payload;
    }
}
