<?php

namespace App\Livewire\Books;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $books = \App\Models\Book::all();
        return view('livewire.books.index', compact('books'));
    }
}
