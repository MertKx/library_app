<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Import\BookImport;
use Illuminate\Support\Facades\Log;

class ProcessBooksImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;

    /**
     * Create a new job instance.
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
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
                Log::error("Import file not found. Tried paths: " . implode(', ', $possiblePaths));
                throw new \Exception("Import file not found. Path: {$this->path}");
            }

            Log::info("Starting import process for file: {$filePath}");

            // Import process
            Excel::import(new BookImport, $filePath);

            Log::info("Import process completed successfully for file: {$filePath}");

            // Optional: Delete file after processing
            // unlink($filePath);

        } catch (\Exception $e) {
            Log::error("Import job failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error("Import job failed permanently: " . $exception->getMessage());
    }
}
