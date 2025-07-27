<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

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
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }
        $book = Book::create($validated);
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
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }
        $book->update($validated);
        return redirect()->route('books.show', $book)->with('success', 'Book Updated Successfully!!');
    }

    public function show(Book $book)
    {
        return view('books.show', compact('book'));
    }

    public function index(Request $request)
    {
        $search = $request->get('search', '');
        
        $books = Book::query()
            ->when($search, function ($query) use ($search) {
                $query->where('book_name', 'like', '%'.$search.'%')
                      ->orWhere('author', 'like', '%'.$search.'%')
                      ->orWhere('isbn', 'like', '%'.$search.'%');
            })
            ->get();
            
        return view('books.index', compact('books', 'search'));
    }

    public function destroy(Book $book)
    {
        $book->delete();
        return redirect()->route('books.index')->with('success', 'Book Deleted Successfully!!');
    }
} 