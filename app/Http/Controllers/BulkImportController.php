<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ProcessBooksImport;
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

            ProcessBooksImport::dispatch($path);

            Log::info("Import job queued for file: {$path}");

            return back()->with('status', 'File uploaded successfully! Process has been added to queue. You can monitor the process status from logs.');

        } catch (\Exception $e) {
            Log::error("Import upload error: " . $e->getMessage());
            return back()->withErrors(['error' => 'File upload error: ' . $e->getMessage()]);
        }
    }

    /**
     * Test import functionality
     */
    public function testImport()
    {
        try {
            $filePath = base_path('test_books.csv');
            
            if (!file_exists($filePath)) {
                return response()->json(['error' => 'Test file not found'], 404);
            }

            // Test the import
            $import = new \App\Import\BookImport();
            \Maatwebsite\Excel\Facades\Excel::import($import, $filePath);
            
            return response()->json([
                'success' => true,
                'message' => 'Test import completed successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
