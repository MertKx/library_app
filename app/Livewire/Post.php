<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Book;

class BookComponent extends Component
{
    public $books, $book_name, $author, $isbn, $cover_image, $bookId;
    public $updateBook = false, $addBook = false;

    protected $listeners = [
        'deleteBookListener' => 'deleteBook'
    ];

    protected $rules = [
        'book_name' => 'required|string|max:255',
        'author' => 'required|string|max:255',
        'isbn' => 'required|string|max:255',
        'cover_image' => 'required|string|max:255', // Dosya türü validasyonu formda yapılmalı
    ];

    public function resetFields()
    {
        $this->book_name = '';
        $this->author = '';
        $this->isbn = '';
        $this->cover_image = '';
    }

    public function render()
    {
        $this->books = Book::select('id', 'book_name', 'author', 'isbn', 'cover_image')->get();
        return view('livewire.book-component');
    }

    public function addBook()
    {
        $this->resetFields();
        $this->addBook = true;
        $this->updateBook = false;
    }

    public function storeBook()
    {
        $this->validate();

        try {
            Book::create([
                'book_name' => $this->book_name,
                'author' => $this->author,
                'isbn' => $this->isbn,
                'cover_image' => $this->cover_image,
            ]);
            session()->flash('success', 'Book Created Successfully!!');
            $this->resetFields();
            $this->addBook = false;
        } catch (\Exception $ex) {
            session()->flash('error', 'Something went wrong!!');
        }
    }

    public function editBook($id)
    {
        try {
            $book = Book::findOrFail($id);
            $this->book_name = $book->book_name;
            $this->author = $book->author;
            $this->isbn = $book->isbn;
            $this->cover_image = $book->cover_image;
            $this->bookId = $book->id;
            $this->updateBook = true;
            $this->addBook = false;
        } catch (\Exception $ex) {
            session()->flash('error', 'Book not found');
        }
    }

    public function updateBook()
    {
        $this->validate();

        try {
            Book::whereId($this->bookId)->update([
                'book_name' => $this->book_name,
                'author' => $this->author,
                'isbn' => $this->isbn,
                'cover_image' => $this->cover_image,
            ]);
            session()->flash('success', 'Book Updated Successfully!!');
            $this->resetFields();
            $this->updateBook = false;
        } catch (\Exception $ex) {
            session()->flash('error', 'Something went wrong!!');
        }
    }

    public function cancelBook()
    {
        $this->addBook = false;
        $this->updateBook = false;
        $this->resetFields();
    }

    public function deleteBook($id)
    {
        try {
            Book::find($id)->delete();
            session()->flash('success', "Book Deleted Successfully!!");
        } catch (\Exception $e) {
            session()->flash('error', "Something went wrong!!");
        }
    }
}
