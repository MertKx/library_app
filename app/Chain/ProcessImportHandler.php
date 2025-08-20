<?php
namespace App\Chain;

class ProcessImportHandler
{
    /**
     * Handle the payload and return it for the next handler
     */
    public function handle(array $payload): array
    {
        $bookImport = $payload['bookImport'];
        $rows = $payload['rows'];

        // Save the data using BookImport
        $bookImport->collection($rows);

        // Return payload for next handler in the pipeline
        return $payload;
    }
}
