<x-layouts.app.sidebar title="Edit Book">
    <flux:main>
        <div class="flex items-center justify-center min-h-[calc(100vh-4rem)] bg-gradient-to-br from-indigo-50 to-zinc-100 dark:from-zinc-900 dark:to-zinc-800 py-12 px-4">
            <form action="{{ route('books.update', $book) }}" method="POST" enctype="multipart/form-data" class="space-y-4 w-full max-w-md bg-white dark:bg-zinc-800 p-8 rounded-xl shadow-2xl">
                @csrf
                @method('PUT')
                @if (session('success'))
                    <div class="bg-green-100 text-green-800 p-2 rounded mb-2">
                        {{ session('success') }}
                    </div>
                @endif

        <div>
            <label for="book_name" class="block font-medium">Book Name</label>
            <input type="text" id="book_name" name="book_name" value="{{ old('book_name', $book->book_name) }}" class="border rounded w-full p-2 mt-1" placeholder="Enter book name">
            @error('book_name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="relative">
            <label for="author_name" class="block font-medium">Author</label>
            <input type="text" id="author_name" name="author_name" value="{{ old('author_name', $book->author ? $book->author->name : '') }}" class="border rounded w-full p-2 mt-1" placeholder="Enter author name" autocomplete="off">
            <div id="author_suggestions" class="absolute z-10 w-full bg-white dark:bg-zinc-700 border border-gray-300 dark:border-zinc-600 rounded-b shadow-lg hidden max-h-60 overflow-y-auto"></div>
            @error('author_name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="isbn" class="block font-medium">ISBN</label>
            <input type="text" id="isbn" name="isbn" value="{{ old('isbn', $book->isbn) }}" class="border rounded w-full p-2 mt-1" placeholder="Enter 13-digit ISBN" maxlength="13" inputmode="numeric">
            @error('isbn') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isbnInput = document.getElementById('isbn');
            if (isbnInput) {
                isbnInput.addEventListener('input', function(e) {
                    let value = this.value.replace(/[^\d]/g, '');
                    if (value.length > 13) {
                        value = value.slice(0, 13);
                    }
                    this.value = value;
                });
            }
        });
        </script>

        <div>
            <label for="cover_image" class="block font-medium">Book Cover Image <span class="text-gray-400 text-xs">(optional, jpg/png/jpeg)</span></label>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-xs text-gray-500 flex-1" id="cover_image_label">
                    @if ($book->cover_image)
                        {{ basename($book->cover_image) }}
                    @else
                        No file chosen
                    @endif
                </span>
                <label class="inline-block cursor-pointer border-2 border-indigo-600 rounded px-3 py-1 bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition">
                    Choose file
                    <input type="file" id="cover_image" name="cover_image" accept=".jpg,.jpeg,.png" class="hidden" aria-label="Choose file" onchange="document.getElementById('cover_image_label').textContent = this.files[0] ? this.files[0].name : '{{ $book->cover_image ? basename($book->cover_image) : 'No file chosen' }}';">
                </label>
            </div>
            @error('cover_image') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            @if ($book->cover_image)
                <div class="mt-2">
                    <img src="{{ asset('storage/' . $book->cover_image) }}" alt="Current Cover" class="h-32 rounded shadow border object-contain">
                </div>
            @endif
        </div>

                <div class="pt-4">
                    <button type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded w-full border-2 border-indigo-700 transition-all duration-200 mt-4
                               hover:bg-indigo-500 hover:border-indigo-400 hover:shadow-lg active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        Update
                    </button>
                </div>
            </form>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const authorInput = document.getElementById('author_name');
            const suggestionsDiv = document.getElementById('author_suggestions');
            let searchTimeout;

            authorInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                if (query.length < 2) {
                    suggestionsDiv.classList.add('hidden');
                    return;
                }

                searchTimeout = setTimeout(() => {
                    fetch(`/search-authors?query=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            suggestionsDiv.innerHTML = '';
                            
                            if (data.length > 0) {
                                data.forEach(author => {
                                    const div = document.createElement('div');
                                    div.className = 'px-4 py-2 hover:bg-gray-100 dark:hover:bg-zinc-600 cursor-pointer border-b border-gray-200 dark:border-zinc-500 text-gray-800 dark:text-gray-200';
                                    div.textContent = author.name;
                                    div.onclick = function() {
                                        authorInput.value = author.name;
                                        suggestionsDiv.classList.add('hidden');
                                    };
                                    suggestionsDiv.appendChild(div);
                                });
                                
                                // "Yeni yazar ekle" butonu
                                const addNewDiv = document.createElement('div');
                                addNewDiv.className = 'px-4 py-2 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-800/50 cursor-pointer border-b border-gray-200 dark:border-zinc-500 font-medium text-blue-700 dark:text-blue-300';
                                addNewDiv.innerHTML = `"${query}" adlı yazarı kaydet ve kullan`;
                                addNewDiv.onclick = function() {
                                    authorInput.value = query;
                                    suggestionsDiv.classList.add('hidden');
                                };
                                suggestionsDiv.appendChild(addNewDiv);
                                
                                suggestionsDiv.classList.remove('hidden');
                            } else {
                                // Hiç sonuç yoksa yeni yazar ekleme seçeneği
                                const addNewDiv = document.createElement('div');
                                addNewDiv.className = 'px-4 py-2 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-800/50 cursor-pointer border-b border-gray-200 dark:border-zinc-500 font-medium text-blue-700 dark:text-blue-300';
                                addNewDiv.innerHTML = `"${query}" adlı yazarı kaydet ve kullan`;
                                addNewDiv.onclick = function() {
                                    authorInput.value = query;
                                    suggestionsDiv.classList.add('hidden');
                                };
                                suggestionsDiv.appendChild(addNewDiv);
                                suggestionsDiv.classList.remove('hidden');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }, 300);
            });

            // Dışarı tıklandığında dropdown'ı kapat
            document.addEventListener('click', function(e) {
                if (!authorInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                    suggestionsDiv.classList.add('hidden');
                }
            });

            // Enter tuşuna basıldığında dropdown'ı kapat
            authorInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    suggestionsDiv.classList.add('hidden');
                }
            });
        });
        </script>
    </flux:main>
</x-layouts.app.sidebar> 