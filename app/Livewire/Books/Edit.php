<?php

namespace App\Livewire\Books;

use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;
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
        $rules = [
            'book_name' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|max:255',
        ];

        // Sadece yeni dosya seçildiyse image validasyonu uygula
        if (
            $this->cover_image && is_object($this->cover_image)
        ) {
            $rules['cover_image'] = 'nullable|image|mimes:jpg,jpeg,png|max:2048';
        }

        $this->validate($rules);

        $data = [
            'book_name' => $this->book_name,
            'author' => $this->author,
            'isbn' => $this->isbn,
        ];

        if ($this->cover_image && is_object($this->cover_image)) {
            $data['cover_image'] = $this->cover_image->store('covers', 'public');
        }

        $this->book->update($data);

        session()->flash('success', 'Book updated successfully!');
        return redirect()->route('books.index');
    }

    public function render()
    {
        return view('livewire.books.edit');
    }
}
