<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ProcessBooksImport;
use App\Models\BulkImportHistory;
use Illuminate\Support\Facades\Log;

class BulkImportController extends Controller
{
    public function index()
    {
        // Check for any import errors from jobs
        $errorKey = 'import_error_' . (request()->get('history_id') ?? '');
        $importError = cache()->get($errorKey);

        if ($importError) {
            cache()->forget($errorKey);
            return view('bulk-import.index')->withErrors(['error' => $importError]);
        }

        return view('bulk-import.index');
    }

    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimetypes:text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet|max:10240'
            ]);

            // Always save to public disk
            $path = $request->file('file')->storePublicly('imports', 'public');
            Log::info('Uploaded file path: ' . $path);
            Log::info('Full file path: ' . storage_path('app/public/' . $path));

            // Check CSV format and duplicates
            $filePath = storage_path('app/public/' . $path);
            $error = $this->validateCsvFile($filePath);

            if ($error) {
                $history = BulkImportHistory::create([
                    'file_name' => $request->file('file')->getClientOriginalName(),
                    'status' => BulkImportHistory::STATUS_FAILED,
                    'error_message' => $error,
                ]);

                return back()->withErrors(['error' => $error . '. You can check the <a href="' . route('bulk-import-history.index') . '" class="text-blue-600 underline">import history page</a> for details.']);
            }

            // Create history record
            $history = BulkImportHistory::create([
                'file_name' => $request->file('file')->getClientOriginalName(),
                'file_path' => $path,
                'status' => BulkImportHistory::STATUS_PENDING,
            ]);

            // Dispatch job with history ID
            ProcessBooksImport::dispatch($path, $history->id);

            Log::info("Import job queued for file: {$path} with history ID: {$history->id}");

            return back()->with('status', 'File uploaded successfully! Process has been added to queue. You can monitor the process status from the <a href="' . route('bulk-import-history.index') . '" class="text-blue-600 underline">import history page</a>.')->with('history_id', $history->id);

        } catch (\Exception $e) {
            Log::error("Import upload error: " . $e->getMessage());
            return back()->withErrors(['error' => 'File upload error: ' . $e->getMessage()]);
        }
    }

    /**
     * Validate CSV file format and content
     */
    private function validateCsvFile($filePath)
    {
        $file = fopen($filePath, 'r');
        if (!$file) {
            return 'Could not read file.';
        }

        // Check header
        $header = fgetcsv($file);
        if (!$header) {
            fclose($file);
            return 'File is empty or invalid CSV format.';
        }

        // Check required columns
        $requiredColumns = ['book_name', 'author_id', 'isbn', 'cover_image'];
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $header)) {
                fclose($file);
                return "Missing column error! Required columns are: " . implode(', ', $requiredColumns);
            }
        }

        // Check for duplicate ISBNs
        $isbnList = [];
        while (($row = fgetcsv($file)) !== false) {
            $isbn = trim($row[2] ?? ''); // ISBN is in 3rd column

            if (empty($isbn)) {
                $isbn = 'TEMP-' . uniqid();
            }

            if (in_array($isbn, $isbnList)) {
                fclose($file);
                return "Duplicate ISBN found in CSV: $isbn";
            }

            $isbnList[] = $isbn;
        }

        fclose($file);
        return null; // No error
    }
}
