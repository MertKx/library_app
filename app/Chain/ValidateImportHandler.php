<?php
namespace App\Chain;

use App\Import\BookImport;

class ValidateImportHandler
{
    /**
     * Handle the payload and return it for next handler
     */
    public function handle(array $payload): array
    {
        $bookImport = $payload['bookImport'];
        $rows = $payload['rows'];

        // Simple validation
        if ($rows->isEmpty()) {
            throw new \Exception('Import file is empty.');
        }

        // You can add more validations here if needed

        // Return the payload for the next handler in pipeline
        return $payload;
    }
}
