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

// Import handler classes
use App\Chain\ValidateImportHandler;
use App\Chain\ProcessImportHandler;
use App\Chain\LogImportHandler;

class ProcessBooksImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $historyId;

    // Handlers for the pipeline
    protected array $handlers;

    public function __construct(
        $path,
        $historyId = null,
        array $handlers = []
    ) {
        $this->path = $path;
        $this->historyId = $historyId;

        // If no handlers are given, use default ones
        $this->handlers = $handlers ?: [
            new ValidateImportHandler(),
            new ProcessImportHandler(),
            new LogImportHandler(),
        ];
    }

    public function handle()
    {
        try {
            // Get or create history record
            $history = $this->getOrCreateHistory();
            $history->update(['status' => BulkImportHistory::STATUS_PROCESSING]);

            // Find the file path
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

            // Read Excel rows
            $rows = Excel::toCollection(new BookImport(), $filePath)->first();

            $bookImport = new BookImport($history);

            // Create payload
            $payload = [
                'bookImport' => $bookImport,
                'rows' => $rows,
                'fileName' => $this->path,
            ];

            // Run pipeline
            foreach ($this->handlers as $handler) {
                $payload = $handler->handle($payload);
            }

            // Update history after success
            $history->update([
                'status' => BulkImportHistory::STATUS_COMPLETED,
                'processed_records' => $bookImport->getProcessedCount(),
                'error_message' => $bookImport->getErrorCount() > 0 ? "Import completed with {$bookImport->getErrorCount()} errors." : null
            ]);

            Log::info("Import process completed successfully for file: {$filePath}.");
        } catch (\Exception $e) {
            // Log error
            Log::error("Import job failed: " . $e->getMessage());

            if (isset($history)) {
                $history->update([
                    'status' => BulkImportHistory::STATUS_FAILED,
                    'error_message' => $e->getMessage()
                ]);
            }

            $errorKey = 'import_error_' . ($this->historyId ?? uniqid());
            cache()->put($errorKey, $e->getMessage(), now()->addMinutes(30));

            throw $e;
        }
    }

    // Get or create history
    private function getOrCreateHistory()
    {
        if ($this->historyId) {
            $history = BulkImportHistory::findOrFail($this->historyId);

            if (!in_array($history->status, [BulkImportHistory::STATUS_PENDING, BulkImportHistory::STATUS_PROCESSING])) {
                throw new \Exception("History record {$this->historyId} is not in a processable state. Current status: {$history->status}");
            }

            return $history;
        }

        // Create new history if not exist
        return BulkImportHistory::create([
            'file_name' => $this->path,
            'status' => BulkImportHistory::STATUS_PENDING
        ]);
    }

    // Find file path in storage
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

    // Handle job failure
    public function failed(\Throwable $exception)
    {
        Log::error("Import job failed permanently: " . $exception->getMessage());

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
