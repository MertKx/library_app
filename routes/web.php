<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

    Volt::route('/', 'books.index')->name('home');

    Route::get('dashboard', function () {
        if (auth()->check()) {
            return redirect()->route('books.index');
        }
        return view('welcome');
    })->name('dashboard');

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
