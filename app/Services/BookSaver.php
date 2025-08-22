<?php
namespace App\Services;

use App\Models\Book;

class BookSaver
{
    public function save(array $bookData): Book
    {
        return Book::create($bookData);
    }
}
