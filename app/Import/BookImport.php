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
    private static ?BookImport $instance = null;

    protected $history;
    protected $processedCount = 0;
    protected $errorCount = 0;

    private function __construct(BulkImportHistory $history = null)
    {
        $this->history = $history;
    }

    // Singleton access point
    public static function getInstance(BulkImportHistory $history = null): BookImport
    {
        if (self::$instance === null) {
            self::$instance = new BookImport($history);
        }
        return self::$instance;
    }

    public function collection(Collection $rows)
    {
        $chunkSize = 100;
        $dataChunk = [];
        $this->processedCount = 0;
        $this->errorCount = 0;

        $defaultAuthor = Author::firstOrCreate(['name' => 'Unknown Author']);

        // Pre-load all authors
        $authorIds = $rows->pluck('author_id')->filter()->unique()->toArray();
        $authors = Author::whereIn('id', $authorIds)->pluck('id')->toArray();

        // ISBN duplicate check
        $isbnList = $rows->pluck('isbn')->map(function($isbn) {
            return empty(trim($isbn)) ? 'TEMP-' . uniqid() : trim($isbn);
        })->filter()->unique()->toArray();

        $csvIsbnCount = count($isbnList);
        $uniqueIsbnCount = count(array_unique($isbnList));
        if ($csvIsbnCount !== $uniqueIsbnCount) {
            $duplicates = array_diff_assoc($isbnList, array_unique($isbnList));
            $duplicateList = implode(', ', array_unique($duplicates));
            throw new \Exception("Duplicate ISBNs found in CSV file: {$duplicateList}");
        }

        if (!empty($isbnList)) {
            $existingIsbns = Book::whereIn('isbn', $isbnList)->pluck('isbn')->toArray();
            if (!empty($existingIsbns)) {
                $existingList = implode(', ', $existingIsbns);
                throw new \Exception("ISBNs already exist in database: {$existingList}");
            }
        }

        // Process data in chunks
        foreach ($rows as $index => $row) {
            try {
                $bookData = $this->prepareBookData($row, $defaultAuthor, $authors);

                if (!$bookData) {
                    $this->errorCount++;
                    continue;
                }

                $dataChunk[] = $bookData;
                $this->processedCount++;

                if (count($dataChunk) === $chunkSize) {
                    $this->insertChunk($dataChunk);
                    $dataChunk = [];
                    $this->updateProgress();
                }
            } catch (\Exception $e) {
                Log::error("Row {$index} processing error: " . $e->getMessage());
                $this->errorCount++;
            }
        }

        if (!empty($dataChunk)) {
            $this->insertChunk($dataChunk);
        }

        if ($this->history) {
            $this->history->update([
                'processed_records' => $this->processedCount
            ]);
        }

        Log::info("Import completed. Processed: {$this->processedCount}, Errors: {$this->errorCount}");
    }

    private function insertChunk(array $dataChunk)
    {
        try {
            Book::query()->upsert(
                $dataChunk,
                ['isbn'],
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

    private function prepareBookData($row, $defaultAuthor, $authors)
    {
        $bookName = $row['book_name'] ?? '';
        $authorId = isset($row['author_id']) ? (int) $row['author_id'] : null;
        $isbn = trim($row['isbn'] ?? '');
        $coverImage = $row['cover_image'] ?? '';

        if (empty($bookName)) {
            Log::warning("Row: Book name is empty, skipping");
            return null;
        }

        if (empty($isbn)) {
            $isbn = 'TEMP-' . uniqid();
        }

        if (empty($authorId) || $authorId <= 0 || !in_array($authorId, $authors)) {
            Log::warning("Row: Author ID {$authorId} not found or invalid, using default author");
            $authorId = $defaultAuthor->id;
        }

        return [
            'book_name'   => $bookName,
            'author_id'   => $authorId,
            'isbn'        => $isbn,
            'cover_image' => $coverImage,
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
