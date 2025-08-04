<?php

namespace App\Import;

use App\Models\Book;
use App\Models\Author;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class BookImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $chunkSize = 100;
        $dataChunk = [];
        $processedCount = 0;
        $errorCount = 0;

        $defaultAuthor = Author::firstOrCreate(['name' => 'Unknown Author']);

        $isbnList = [];

        // 1. Check for duplicate ISBNs within CSV file
        foreach ($rows as $index => $row) {
            $isbn = trim($row['isbn'] ?? '');

            if (empty($isbn)) {
                $isbn = 'TEMP-' . uniqid();
            }

            if (in_array($isbn, $isbnList)) {
                throw new \Exception("Duplicate ISBN found in CSV: $isbn");
            }

            $isbnList[] = $isbn;
        }

        // 2. Check if ISBNs already exist in database
        $exists = Book::whereIn('isbn', $isbnList)->exists();
        if ($exists) {
            throw new \Exception("ISBNs already exist in database, import cancelled.");
        }

        // 3. Insert data in chunks
        foreach ($rows as $index => $row) {
            try {
                $bookName = $row['book_name'] ?? '';
                $authorId = isset($row['author_id']) ? (int) $row['author_id'] : null;
                $isbn = trim($row['isbn'] ?? '');

                if (empty($isbn)) {
                    $isbn = 'TEMP-' . uniqid();
                }

                $coverImage = $row['cover_image'] ?? '';

                if (empty($bookName)) {
                    Log::warning("Row {$index}: Book name is empty, skipping");
                    $errorCount++;
                    continue;
                }

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

                $processedCount++;

                if (count($dataChunk) === $chunkSize) {
                    $this->insertChunk($dataChunk);
                    $dataChunk = [];
                }

            } catch (\Exception $e) {
                Log::error("Row {$index} processing error: " . $e->getMessage());
                $errorCount++;
            }
        }

        if (!empty($dataChunk)) {
            $this->insertChunk($dataChunk);
        }

        Log::info("Import completed. Processed: {$processedCount}, Errors: {$errorCount}");
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
}
