<?php

namespace App\Livewire\Books;

use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public $book_name;
    public $author;
    public $isbn;
    public $cover_image;

    protected $rules = [
        'book_name' => 'required|string|max:255',
        'author' => 'required|string|max:255',
        'isbn' => 'required|string|max:255',
        'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ];

    public function store()
    {
        $validated = $this->validate();

        if ($this->cover_image) {
            $validated['cover_image'] = $this->cover_image->store('covers', 'public');
        } else {
            $validated['cover_image'] = null;
        }

        \App\Models\Book::create($validated);
        session()->flash('success', 'Book created successfully!');
        $this->reset(['book_name', 'author', 'isbn', 'cover_image']);
    }

    public function render()
    {
        return view('livewire.books.create');
    }
}
