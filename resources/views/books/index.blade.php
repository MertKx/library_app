<x-layouts.app.sidebar title="Library">
    <flux:main>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">Library</h1>
            <div class="flex gap-2 items-center">
                @auth
                <a href="{{ route('books.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded shadow transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Book
                </a>
                @endauth
            </div>
        </div>

    @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" class="mb-4 px-4 py-2 rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 text-center transition-all duration-500">
            {{ session('success') }}
        </div>
    @endif



    @if($books->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($books as $book)
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow p-4 flex flex-col">
                    <div class="flex justify-center mb-4">
                        @if($book->cover_image)
                            <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->book_name }}" class="h-40 w-32 object-cover rounded" />
                        @else
                            <div class="h-40 w-32 flex items-center justify-center bg-zinc-200 dark:bg-zinc-700 rounded text-zinc-400">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75V17.25C3 18.4926 4.00736 19.5 5.25 19.5H18.75C19.9926 19.5 21 18.4926 21 17.25V6.75M3 6.75C3 5.50736 4.00736 4.5 5.25 4.5H18.75C19.9926 4.5 21 5.50736 21 6.75M3 6.75V8.25C3 9.49264 4.00736 10.5 5.25 10.5H18.75C19.9926 10.5 21 9.49264 21 8.25V6.75" />
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 flex flex-col gap-1">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $book->book_name }}</h2>
                        <p class="text-sm text-zinc-600 dark:text-zinc-300">Author: {{ $book->author ? $book->author->name : 'Unknown' }}</p>
                        <p class="text-xs text-zinc-400">ISBN: {{ $book->isbn }}</p>
                        @if($book->stores->count() > 0)
                            <div class="mt-2">
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-1">Available in:</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($book->stores as $store)
                                        <span class="inline-block bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-xs px-2 py-1 rounded">{{ $store->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('books.show', $book) }}" class="flex-1 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 px-3 py-1 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-center transition">Details</a>
                        @auth
                        <a href="{{ route('books.edit', $book) }}" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-center transition">Edit</a>
                        <form action="{{ route('books.destroy', $book) }}" method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to delete this book?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-center transition">Delete</button>
                        </form>
                        @endauth
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center text-zinc-500 dark:text-zinc-400 py-12">
            @auth
                There is no book yet.
            @else
                <div class="space-y-4">
                    <p>There is no book yet.</p>
                    <p class="text-sm">Login to add the first book to the library!</p>
                    <a href="{{ route('login') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded shadow transition">
                        Login to Add Books
                    </a>
                </div>
            @endauth
        </div>
    @endif
    </flux:main>
</x-layouts.app.sidebar>
