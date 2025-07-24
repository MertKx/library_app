<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Livewire\Books\Create;


    Volt::route('/books', 'books.index')->name('books.index');
    Route::get('/books/create', Create::class)->name('books.create');


    Route::get('/', function () {
        return redirect()->route('books.index');
    })->name('home');

    Volt::route('books', 'books.index')->name('books.index');
    Volt::route('books/{book}', 'books.show')->name('books.show');

    Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');


    Volt::route('books/create', 'books.create')->name('books.create');
    Volt::route('books/{book}/edit', 'books.edit')->name('books.edit');
});


require __DIR__.'/auth.php';
