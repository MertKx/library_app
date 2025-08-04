<?php

use App\Http\Controllers\BulkImportController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

    Route::get('/', function () {
        return redirect()->route('books.index');
    })->name('home');

    Route::get('/books', [App\Http\Controllers\BookController::class, 'index'])->name('books.index');

    Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    //Auth page
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Route::get('/books/create', [App\Http\Controllers\BookController::class, 'create'])->name('books.create');
    Route::post('/books', [App\Http\Controllers\BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [App\Http\Controllers\BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [App\Http\Controllers\BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [App\Http\Controllers\BookController::class, 'destroy'])->name('books.destroy');

    //Bulk Import Page
    Route::get('/bulk-import', [BulkImportController::class, 'index'])->name('bulk-import.index');
    Route::post('/bulk-import', [BulkImportController::class, 'upload'])->name('bulk-import.upload');

    // Search author endpoint
    Route::get('/search-authors', [App\Http\Controllers\BookController::class, 'searchAuthors'])->name('authors.search');

    // Test import
    Route::get('/test-import', [BulkImportController::class, 'testImport'])->name('test-import');
});
    Route::get('/books/{book}', [App\Http\Controllers\BookController::class, 'show'])->name('books.show');


require __DIR__.'/auth.php';
