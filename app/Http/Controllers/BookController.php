<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Author;

class BookController extends Controller
{
    public function create()
    {
        return view('books.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_name' => 'required|string|max:255',
            'author_name' => 'required|string|max:255',
            'isbn' => 'required|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Yazarı bul veya oluştur (case insensitive)
        $author = Author::whereRaw('LOWER(name) = ?', [strtolower($validated['author_name'])])->first();
        
        if (!$author) {
            $author = Author::create(['name' => $validated['author_name']]);
        }

        $bookData = [
            'book_name' => $validated['book_name'],
            'author_id' => $author->id,
            'isbn' => $validated['isbn'],
        ];

        if ($request->hasFile('cover_image')) {
            $bookData['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $book = Book::create($bookData);
        return redirect()->route('books.show', $book)->with('success', 'Book Created Successfully!!');
    }

    public function edit(Book $book)
    {
        return view('books.edit', compact('book'));
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'book_name' => 'required|string|max:255',
            'author_name' => 'required|string|max:255',
            'isbn' => 'required|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Yazarı bul veya oluştur (case insensitive)
        $author = Author::whereRaw('LOWER(name) = ?', [strtolower($validated['author_name'])])->first();
        
        if (!$author) {
            $author = Author::create(['name' => $validated['author_name']]);
        }

        $bookData = [
            'book_name' => $validated['book_name'],
            'author_id' => $author->id,
            'isbn' => $validated['isbn'],
        ];

        if ($request->hasFile('cover_image')) {
            $bookData['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $book->update($bookData);
        return redirect()->route('books.show', $book)->with('success', 'Book Updated Successfully!!');
    }

    public function show(Book $book)
    {
        return view('books.show', compact('book'));
    }

    public function index(Request $request)
    {
        $search = $request->get('search', '');
        
        $books = Book::with('author')
            ->when($search, function ($query) use ($search) {
                $query->where('book_name', 'like', '%'.$search.'%')
                      ->orWhere('isbn', 'like', '%'.$search.'%')
                      ->orWhereHas('author', function ($q) use ($search) {
                          $q->where('name', 'like', '%'.$search.'%');
                      });
            })
            ->get();
            
        return view('books.index', compact('books', 'search'));
    }

    public function destroy(Book $book)
    {
        $book->delete();
        return redirect()->route('books.index')->with('success', 'Book Deleted Successfully!!');
    }

    public function searchAuthors(Request $request)
    {
        $query = $request->get('query', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $authors = Author::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($query) . '%'])
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($authors);
    }
} 