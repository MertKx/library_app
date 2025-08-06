<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BulkImportHistory;

class BulkImportHistoryController extends Controller
{
    /**
     * Display a listing of the bulk import history.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = BulkImportHistory::query();

        // Sort by
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $histories = $query->paginate(15)->withQueryString();

        // Get statistics
        $stats = [
            'total' => BulkImportHistory::count(),
            'completed' => BulkImportHistory::byStatus(BulkImportHistory::STATUS_COMPLETED)->count(),
            'failed' => BulkImportHistory::byStatus(BulkImportHistory::STATUS_FAILED)->count(),
            'pending' => BulkImportHistory::byStatus(BulkImportHistory::STATUS_PENDING)->count(),
            'processing' => BulkImportHistory::byStatus(BulkImportHistory::STATUS_PROCESSING)->count(),
        ];

        return view('bulk-import-history.index', compact('histories', 'stats'));
    }

    /**
     * Show the details of a specific import history.
     *
     * @param BulkImportHistory $history
     * @return \Illuminate\View\View
     */
    public function show(BulkImportHistory $history)
    {
        return view('bulk-import-history.show', compact('history'));
    }

    /**
     * Retry a failed import.
     *
     * @param BulkImportHistory $history
     * @return \Illuminate\Http\RedirectResponse
     */
    public function retry(BulkImportHistory $history)
    {
        if ($history->status !== BulkImportHistory::STATUS_FAILED) {
            return back()->withErrors(['error' => 'Only failed imports can be retried.']);
        }

        // Check if there's already a pending or processing record for this file
        $activeImport = BulkImportHistory::where('file_name', $history->file_name)
            ->whereIn('status', [BulkImportHistory::STATUS_PENDING, BulkImportHistory::STATUS_PROCESSING])
            ->where('id', '!=', $history->id)
            ->first();

        if ($activeImport) {
            return back()->withErrors(['error' => 'There is already an active import for this file.']);
        }

        // Reset status to pending
        $history->update([
            'status' => BulkImportHistory::STATUS_PENDING,
            'processed_records' => 0,
            'error_message' => null,
        ]);

        // Dispatch the job again
        \App\Jobs\ProcessBooksImport::dispatch($history->file_name);
        // Dispatch the job again with the history ID
        \App\Jobs\ProcessBooksImport::dispatch($history->file_path, $history->id);

        return back()->with('status', 'Import has been queued for retry.');
    }
}
