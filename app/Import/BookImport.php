<?php

namespace App\Import;

use App\Models\Book;
use App\Models\Author;
use App\Models\BulkImportHistory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class BookImport implements ToCollection, WithHeadingRow
{
    protected $history;
    protected $processedCount = 0;
    protected $errorCount = 0;

    public function __construct(BulkImportHistory $history = null)
    {
        $this->history = $history;
    }

    public function collection(Collection $rows)
    {
        $chunkSize = 100;
        $dataChunk = [];
        $this->processedCount = 0;
        $this->errorCount = 0;

        // Get or create a default author
        $defaultAuthor = Author::firstOrCreate(['name' => 'Unknown Author']);

        foreach ($rows as $index => $row) {
            try {
                // Normalize column names and handle missing columns
                $bookName = $row['book_name'] ?? $row['book_name'] ?? '';
                $authorId = isset($row['author_id']) ? (int) $row['author_id'] : null;
                $isbn = $row['isbn'] ?? '';
                $coverImage = $row['cover_image'] ?? '';

                // Check required fields
                if (empty($bookName)) {
                    Log::warning("Row {$index}: Book name is empty, skipping");
                    $this->errorCount++;
                    continue;
                }

                // If isbn is empty, generate a fake one to ensure upsert works
                if (empty($isbn)) {
                    $isbn = 'TEMP-' . uniqid();
                }

                // Handle author_id - if it doesn't exist or invalid, use default author
                if (empty($authorId) || $authorId <= 0 || !Author::find($authorId)) {
                    Log::warning("Row {$index}: Author ID {$authorId} not found or invalid, using default author");
                    $authorId = $defaultAuthor->id;
                }

                $dataChunk[] = [
                    'book_name'   => $bookName,
                    'author_id'   => $authorId,
                    'isbn'        => $isbn,
                    'cover_image' => $coverImage,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];

                $this->processedCount++;

                if (count($dataChunk) === $chunkSize) {
                    $this->insertChunk($dataChunk);
                    $dataChunk = [];
                    
                    // Update progress in history
                    $this->updateProgress();
                }

            } catch (\Exception $e) {
                Log::error("Row {$index} processing error: " . $e->getMessage());
                $this->errorCount++;
            }
        }

        // Insert remaining data
        if (!empty($dataChunk)) {
            $this->insertChunk($dataChunk);
        }

        // Final progress update
        $this->updateProgress();

        Log::info("Import completed. Processed: {$this->processedCount}, Errors: {$this->errorCount}");
    }

    private function insertChunk(array $dataChunk)
    {
        try {
            Book::query()->upsert(
                $dataChunk,
                ['isbn'], // Unique key
                ['book_name', 'author_id', 'cover_image', 'updated_at']
            );
            Log::info("Inserted chunk of size: " . count($dataChunk));
        } catch (\Exception $e) {
            Log::error("Chunk insert error: " . $e->getMessage());
            throw $e;
        }
    }

    private function updateProgress()
    {
        if ($this->history) {
            $this->history->update([
                'processed_records' => $this->processedCount
            ]);
        }
    }

    public function getProcessedCount()
    {
        return $this->processedCount;
    }

    public function getErrorCount()
    {
        return $this->errorCount;
    }
}
