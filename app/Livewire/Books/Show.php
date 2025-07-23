<?php

namespace App\Livewire\Books;

use Livewire\Component;

class Show extends Component
{
    public $book;

    public function mount(\App\Models\Book $book)
    {
        $this->book = $book;
    }

    public function render()
    {
        return view('livewire.books.show');
    }
}
