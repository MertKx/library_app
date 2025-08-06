<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Import\BookImport;
use App\Models\BulkImportHistory;
use Illuminate\Support\Facades\Log;

class ProcessBooksImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $historyId;

    /**
     * Create a new job instance.
     */
    public function __construct($path, $historyId = null)
    {
        $this->path = $path;
        $this->historyId = $historyId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            // Create or update history record
            $history = $this->getOrCreateHistory();
            $history->update(['status' => BulkImportHistory::STATUS_PROCESSING]);

            // Find file path
            $filePath = $this->findFilePath();
            if (!$filePath) {
                $errorMessage = "Import file not found.";
                Log::error($errorMessage);
                $history->update([
                    'status' => BulkImportHistory::STATUS_FAILED,
                    'error_message' => $errorMessage
                ]);
                throw new \Exception($errorMessage);
            }

            Log::info("Starting import process for file: {$filePath}");

            // Get total records count
            $totalRecords = $this->countTotalRecords($filePath);
            $history->update(['total_records' => $totalRecords]);

            // Import process with progress tracking
            $import = new BookImport($history);
            Excel::import($import, $filePath);

            // Update final status with accurate counts
            $finalProcessedCount = $import->getProcessedCount();
            $finalErrorCount = $import->getErrorCount();

            $history->update([
                'status' => BulkImportHistory::STATUS_COMPLETED,
                'processed_records' => $finalProcessedCount,
                'error_message' => $finalErrorCount > 0 ? "Import completed with {$finalErrorCount} errors." : null
            ]);

            Log::info("Import process completed successfully for file: {$filePath}. Processed: {$finalProcessedCount}, Errors: {$finalErrorCount}");

            // Optional: Delete file after processing
            // unlink($filePath);

        } catch (\Exception $e) {
            Log::error("Import job failed: " . $e->getMessage());

            // Update history with error
            if (isset($history)) {
                $history->update([
                    'status' => BulkImportHistory::STATUS_FAILED,
                    'error_message' => $e->getMessage()
                ]);
            }

            // Store error in cache for bulk import page to display
            $errorKey = 'import_error_' . ($this->historyId ?? uniqid());
            cache()->put($errorKey, $e->getMessage(), now()->addMinutes(30));

            throw $e;
        }
    }

    /**
     * Get or create history record
     */
    private function getOrCreateHistory()
    {
        if ($this->historyId) {
            return BulkImportHistory::findOrFail($this->historyId);
            $history = BulkImportHistory::findOrFail($this->historyId);

            // Double check that this history record is in a state that can be processed
            if (!in_array($history->status, [BulkImportHistory::STATUS_PENDING, BulkImportHistory::STATUS_PROCESSING])) {
                throw new \Exception("History record {$this->historyId} is not in a processable state. Current status: {$history->status}");
            }

            return $history;
        }
    }

    /**
     * Find the correct file path
     */
    private function findFilePath()
    {
        $possiblePaths = [
            storage_path('app/public/' . $this->path),
            storage_path('app/' . $this->path),
            public_path('storage/' . $this->path),
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Count total records in file
     */
    private function countTotalRecords($filePath)
    {
        try {
            // Use a simple class without WithHeadingRow to get actual row count
            $rows = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
                public function array(array $array)
                {
                    return $array;
                }
            }, $filePath);

            // Subtract 1 for header row
            return max(0, count($rows[0]) - 1);
        } catch (\Exception $e) {
            Log::warning("Could not count total records: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error("Import job failed permanently: " . $exception->getMessage());

        // Update history if exists
        if ($this->historyId) {
            $history = BulkImportHistory::find($this->historyId);
            if ($history) {
                $history->update([
                    'status' => BulkImportHistory::STATUS_FAILED,
                    'error_message' => $exception->getMessage()
                ]);
            }
        }
    }
}
