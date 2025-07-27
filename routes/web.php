<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;


    Route::get('/', function () {
        return redirect()->route('books.index');
    })->name('home');

    /* Route::get('/create', function () {
        return redirect()->route('books.create');
    }); */

        Route::get('/books', [App\Http\Controllers\BookController::class, 'index'])->name('books.index');
        Route::get('/books/{book}', [App\Http\Controllers\BookController::class, 'show'])->name('books.show');

        Route::middleware(['auth'])->group(callback: function () {
        Route::redirect('settings', 'settings/profile');

        Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
        Volt::route('settings/password', 'settings.password')->name('settings.password');
        Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

        Route::get('/books/create', [App\Http\Controllers\BookController::class, 'create'])->name('books.create');
        Route::post('/books', [App\Http\Controllers\BookController::class, 'store'])->name('books.store');
        Route::get('/books/{book}/edit', [App\Http\Controllers\BookController::class, 'edit'])->name('books.edit');
        Route::put('/books/{book}', [App\Http\Controllers\BookController::class, 'update'])->name('books.update');
        Route::delete('/books/{book}', [App\Http\Controllers\BookController::class, 'destroy'])->name('books.destroy');
    });


require __DIR__.'/auth.php';
