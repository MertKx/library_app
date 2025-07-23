<?php

namespace App\Livewire\Books;

use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    public $search = '';

    public function render()
    {
        $books = \App\Models\Book::query()
            ->when($this->search, function ($query) {
                $query->where('book_name', 'like', '%'.$this->search.'%')
                      ->orWhere('author', 'like', '%'.$this->search.'%')
                      ->orWhere('isbn', 'like', '%'.$this->search.'%');
            })
            ->get();
        return view('livewire.books.index', compact('books'));
    }
}
