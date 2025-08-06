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

            // Create history record
            $history = BulkImportHistory::create([
                'file_name' => $request->file('file')->getClientOriginalName(),
                'file_path' => $path,
                'status' => BulkImportHistory::STATUS_PENDING,
            ]);

            // Dispatch job with history ID
            ProcessBooksImport::dispatch($path, $history->id);

            Log::info("Import job queued for file: {$path} with history ID: {$history->id}");

            return back()->with('status', 'File uploaded successfully! Process has been added to queue. You can monitor the process status from the import history page.');

        } catch (\Exception $e) {
            Log::error("Import upload error: " . $e->getMessage());
            return back()->withErrors(['error' => 'File upload error: ' . $e->getMessage()]);
        }
    }
}
