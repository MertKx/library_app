<?php

namespace App\Livewire\Books;

use Livewire\Component;

class Edit extends Component
{
    public $book;
    public $book_name;
    public $author;
    public $isbn;
    public $cover_image;

    public function mount(\App\Models\Book $book)
    {
        $this->book = $book;
        $this->book_name = $book->book_name;
        $this->author = $book->author;
        $this->isbn = $book->isbn;
        $this->cover_image = $book->cover_image;
    }

    public function update()
    {
        $this->validate([
            'book_name' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        $this->book->update([
            'book_name' => $this->book_name,
            'author' => $this->author,
            'isbn' => $this->isbn,
            // 'cover_image' => $this->cover_image,
        ]);
        session()->flash('success', 'Book updated successfully!');
        return redirect()->route('books.index');
    }

    public function render()
    {
        return view('livewire.books.edit');
    }
}
