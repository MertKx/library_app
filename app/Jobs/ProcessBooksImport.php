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

            // Try different possible file paths
            $possiblePaths = [
                storage_path('app/public/' . $this->path), // For storePublicly
                storage_path('app/' . $this->path), // For store
                public_path('storage/' . $this->path), // For public storage
            ];

            $filePath = null;
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $filePath = $path;
                    break;
                }
            }

            if (!$filePath) {
                $errorMessage = "Import file not found. Tried paths: " . implode(', ', $possiblePaths);
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

            // Update final status
            $history->update([
                'status' => BulkImportHistory::STATUS_COMPLETED,
                'processed_records' => $import->getProcessedCount()
            ]);

            Log::info("Import process completed successfully for file: {$filePath}");

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
        }

        // Extract filename from path
        $fileName = basename($this->path);
        
        return BulkImportHistory::create([
            'file_name' => $fileName,
            'status' => BulkImportHistory::STATUS_PENDING,
        ]);
    }

    /**
     * Count total records in file
     */
    private function countTotalRecords($filePath)
    {
        try {
            $rows = Excel::toArray(new \App\Import\BookImport, $filePath);
            return count($rows[0]) - 1; // Subtract header row
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
