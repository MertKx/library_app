<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Public (herkese açık) kitap rotaları
Volt::route('books', 'books.index')->name('books.index');
Volt::route('books/{book}', 'books.show')->name('books.show');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Sadece giriş yapmış kullanıcılar için kitap ekle/düzenle
    Volt::route('books/create', 'books.create')->name('books.create');
    Volt::route('books/{book}/edit', 'books.edit')->name('books.edit');
});


require __DIR__.'/auth.php';
