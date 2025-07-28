<x-layouts.app.sidebar title="{{ $book->book_name }}">
    <flux:main>
        <div class="flex items-center justify-center min-h-[calc(100vh-4rem)] bg-gradient-to-br from-indigo-50 to-zinc-100 dark:from-zinc-900 dark:to-zinc-800 py-12 px-4">
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-2xl p-8 max-w-md w-full flex flex-col items-center gap-6">
        <div class="w-40 h-56 flex items-center justify-center bg-zinc-200 dark:bg-zinc-800 rounded-lg overflow-hidden shadow">
            @if($book->cover_image)
                <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->book_name }}" class="object-cover w-full h-full" />
            @else
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 text-zinc-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75V17.25C3 18.4926 4.00736 19.5 5.25 19.5H18.75C19.9926 19.5 21 18.4926 21 17.25V6.75M3 6.75C3 5.50736 4.00736 4.5 5.25 4.5H18.75C19.9926 4.5 21 5.50736 21 6.75M3 6.75V8.25C3 9.49264 4.00736 10.5 5.25 10.5H18.75C19.9926 10.5 21 9.49264 21 8.25V6.75" />
                </svg>
            @endif
        </div>
        <div class="w-full flex flex-col items-center gap-2">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white text-center">{{ $book->book_name }}</h1>
            <p class="text-md text-zinc-600 dark:text-zinc-300">by <span class="font-semibold">{{ $book->author ? $book->author->name : 'Unknown' }}</span></p>
            <p class="text-xs text-zinc-400">ISBN: {{ $book->isbn }}</p>
            <p class="text-xs text-zinc-400 mt-2">Added: {{ $book->created_at?->format('d M Y') }}</p>
        </div>
                <div class="flex gap-2 w-full">
                    <a href="{{ route('books.index') }}" class="flex-1 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 px-4 py-2 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-center transition">Back to List</a>
                    @auth
                    <a href="{{ route('books.edit', $book) }}" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-center transition">Edit</a>
                    <form action="{{ route('books.destroy', $book) }}" method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to delete this book?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-center transition">Delete</button>
                    </form>
                    @endauth
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar> 