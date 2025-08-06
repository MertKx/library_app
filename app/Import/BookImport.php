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
    protected $skippedCount = 0;
    protected $duplicateCount = 0;
    protected $errors = [];

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
        $this->skippedCount = 0;
        $this->duplicateCount = 0;
        $this->errors = [];

        $defaultAuthor = Author::firstOrCreate(['name' => 'Unknown Author']);

        $csvIsbnList = [];
        $validRows = [];

        // 1. First pass: Validate rows and check for duplicate ISBNs within CSV
        foreach ($rows as $index => $row) {
            try {
                $isbn = trim($row['isbn'] ?? '');
                $bookName = trim($row['book_name'] ?? '');

                // Skip rows with empty book name
                if (empty($bookName)) {
                    $this->logError($index + 2, "Book name is empty, skipping row");
                    $this->skippedCount++;
                    continue;
                }

                // Generate unique ISBN if empty
                if (empty($isbn)) {
                    $isbn = 'TEMP-' . uniqid();
                }

                // Check for duplicate ISBNs within CSV file
                if (in_array($isbn, $csvIsbnList)) {
                    $this->logError($index + 2, "Duplicate ISBN found in CSV: $isbn, skipping row");
                    $this->duplicateCount++;
                    continue;
                }

                $csvIsbnList[] = $isbn;
                $validRows[] = [
                    'index' => $index + 2, // +2 because index starts at 0 and we have header row
                    'data' => $row,
                    'isbn' => $isbn,
                    'book_name' => $bookName
                ];

            } catch (\Exception $e) {
                $this->logError($index + 2, "Row validation error: " . $e->getMessage());
                $this->errorCount++;
            }
        }

        // 2. Check which ISBNs already exist in database
        $existingIsbns = [];
        if (!empty($csvIsbnList)) {
            $existingIsbns = Book::whereIn('isbn', $csvIsbnList)->pluck('isbn')->toArray();
        }

        // 3. Process valid rows
        foreach ($validRows as $rowData) {
            try {
                $row = $rowData['data'];
                $isbn = $rowData['isbn'];
                $bookName = $rowData['book_name'];
                $index = $rowData['index'];

                // Skip if ISBN already exists in database
                if (in_array($isbn, $existingIsbns)) {
                    $this->logError($index, "ISBN already exists in database: $isbn, skipping row");
                    $this->duplicateCount++;
                    continue;
                }

                $authorId = isset($row['author_id']) ? (int) $row['author_id'] : null;
                $coverImage = $row['cover_image'] ?? '';

                // Validate and handle author ID
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
                $this->logError($rowData['index'], "Row processing error: " . $e->getMessage());
                $this->errorCount++;
            }
        }

        // Insert remaining data
        if (!empty($dataChunk)) {
            $this->insertChunk($dataChunk);
        }

        // Final progress update
        $this->updateProgress();

        Log::info("Import completed. Processed: {$this->processedCount}, Errors: {$this->errorCount}, Skipped: {$this->skippedCount}, Duplicates: {$this->duplicateCount}");
        
        // Log summary of errors if any
        if (!empty($this->errors)) {
            Log::info("Import errors summary:", $this->errors);
        }
    }

    private function insertChunk(array $dataChunk)
    {
        try {
            // Use insert instead of upsert to avoid updating existing records
            Book::insert($dataChunk);
            Log::info("Inserted chunk of size: " . count($dataChunk));
        } catch (\Exception $e) {
            Log::error("Chunk insert error: " . $e->getMessage());
            
            // Try to insert records one by one to identify specific failures
            foreach ($dataChunk as $record) {
                try {
                    Book::insert([$record]);
                } catch (\Exception $singleError) {
                    $this->logError('chunk', "Failed to insert record with ISBN {$record['isbn']}: " . $singleError->getMessage());
                    $this->errorCount++;
                    $this->processedCount--; // Reduce processed count since this record failed
                }
            }
        }
    }

    private function updateProgress()
    {
        if ($this->history) {
            $errorMessage = null;
            if (!empty($this->errors)) {
                $errorMessage = "Errors: " . $this->errorCount . ", Skipped: " . $this->skippedCount . ", Duplicates: " . $this->duplicateCount;
                if (count($this->errors) <= 10) {
                    $errorMessage .= "\nDetails: " . implode("; ", array_slice($this->errors, 0, 10));
                } else {
                    $errorMessage .= "\nShowing first 10 errors: " . implode("; ", array_slice($this->errors, 0, 10));
                }
            }

            $this->history->update([
                'processed_records' => $this->processedCount,
                'error_message' => $errorMessage
            ]);
        }
    }

    private function logError($rowNumber, $message)
    {
        $errorMsg = "Row {$rowNumber}: {$message}";
        Log::warning($errorMsg);
        $this->errors[] = $errorMsg;
    }

    public function getProcessedCount()
    {
        return $this->processedCount;
    }

    public function getErrorCount()
    {
        return $this->errorCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }

    public function getDuplicateCount()
    {
        return $this->duplicateCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
