<?php
namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\Log;

class LoggingBookSaver
{
    protected $bookSaver;

    public function __construct(BookSaver $bookSaver)
    {
        $this->bookSaver = $bookSaver;
    }

    public function save(array $bookData): Book
    {
        $book = $this->bookSaver->save($bookData);

        Log::info("A book created by hand via Decorator: {$book->book_name} (ISBN: {$book->isbn})");

        return $book;
    }
}
