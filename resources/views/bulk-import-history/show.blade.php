<x-layouts.app.sidebar title="Import Details">
    <flux:main>
        <div class="p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Import Details</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">ID: {{ $history->id }}</p>
                </div>
                <a href="{{ route('bulk-import-history.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow transition">
                    Back to History
                </a>
            </div>

            <!-- Status Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Status</h2>
                        <span class="px-3 py-1 text-sm font-medium rounded-full {{ $history->getStatusBadgeClass() }}">
                            {{ $history->status }}
                        </span>
                    </div>
                    @if($history->status === 'Failed')
                        <form method="POST" action="{{ route('bulk-import-history.retry', $history) }}">
                            @csrf
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow transition">
                                Retry Import
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- File Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">File Information</h3>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">File Name:</span>
                            <p class="text-gray-900 dark:text-white">{{ $history->file_name }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Created At:</span>
                            <p class="text-gray-900 dark:text-white">{{ $history->created_at->format('F d, Y \a\t H:i:s') }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Updated At:</span>
                            <p class="text-gray-900 dark:text-white">{{ $history->updated_at->format('F d, Y \a\t H:i:s') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Progress Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Progress Information</h3>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Records:</span>
                            <p class="text-gray-900 dark:text-white">{{ $history->total_records ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Processed Records:</span>
                            <p class="text-gray-900 dark:text-white">{{ $history->processed_records ?? 'N/A' }}</p>
                        </div>
                        @if($history->total_records > 0)
                            <div>
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Success Rate:</span>
                                <p class="text-gray-900 dark:text-white">{{ $history->getSuccessRate() }}%</p>
                            </div>
                        @endif
                        @if($history->getErrorCount() > 0)
                            <div>
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Error Count:</span>
                                <p class="text-red-600 dark:text-red-400">{{ $history->getErrorCount() }}</p>
                            </div>
                        @endif
                        <div>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Status Icon:</span>
                            <p class="text-3xl mt-1">
                                @if($history->status === 'Completed')
                                    ✅
                                @elseif($history->status === 'Processing' || $history->status === 'Pending')
                                    ⏳
                                @elseif($history->status === 'Failed')
                                    ❌
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Error Message -->
            @if($history->error_message)
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-red-900 dark:text-red-100 mb-2">Error Details</h3>
                    <div class="bg-white dark:bg-gray-800 rounded p-4">
                        <pre class="text-sm text-red-800 dark:text-red-200 whitespace-pre-wrap">{{ $history->error_message }}</pre>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actions</h3>
                <div class="flex gap-4">
                    <a href="{{ route('bulk-import-history.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow transition">
                        Back to History
                    </a>
                    @if($history->status === 'Failed')
                        <form method="POST" action="{{ route('bulk-import-history.retry', $history) }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow transition">
                                Retry Import
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>
