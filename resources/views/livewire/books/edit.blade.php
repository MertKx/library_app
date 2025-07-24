<div class="flex items-center justify-center min-h-screen bg-white dark:bg-zinc-900">
    <form wire:submit.prevent="update" class="space-y-4 w-full max-w-md bg-white dark:bg-zinc-800 p-8 rounded shadow">
        @if (session()->has('success'))
            <div class="bg-green-100 text-green-800 p-2 rounded mb-2">
                {{ session('success') }}
            </div>
        @endif

        <div>
            <label for="book_name" class="block font-medium">Book Name</label>
            <input type="text" id="book_name" wire:model.defer="book_name" class="border rounded w-full p-2 mt-1" placeholder="Enter book name">
            @error('book_name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="author" class="block font-medium">Author</label>
            <input type="text" id="author" wire:model.defer="author" class="border rounded w-full p-2 mt-1" placeholder="Enter author">
            @error('author') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="isbn" class="block font-medium">ISBN</label>
            <input type="text" id="isbn" wire:model.defer="isbn" class="border rounded w-full p-2 mt-1" placeholder="xxx-x-xxx-xxxxx-x" maxlength="17" inputmode="numeric" pattern="^\d{3}-\d-\d{3}-\d{5}-\d$" title="ISBN must be in the format xxx-x-xxx-xxxxx-x">
            @error('isbn') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isbnInput = document.getElementById('isbn');
            if (isbnInput) {
                isbnInput.addEventListener('input', function(e) {
                    let value = this.value.replace(/[^\d]/g, '');
                    if (value.length > 13) value = value.slice(0, 13);
                    let formatted = '';
                    if (value.length > 0) formatted += value.slice(0, 3);
                    if (value.length > 3) formatted += '-' + value.slice(3, 4);
                    if (value.length > 4) formatted += '-' + value.slice(4, 7);
                    if (value.length > 7) formatted += '-' + value.slice(7, 12);
                    if (value.length > 12) formatted += '-' + value.slice(12, 13);
                    this.value = formatted;
                });
            }
        });
        </script>

        <div class="pt-4">
            <button type="submit"
                class="bg-indigo-600 text-white px-4 py-2 rounded w-full border-2 border-indigo-700 transition-all duration-200 mt-4
                       hover:bg-indigo-500 hover:border-indigo-400 hover:shadow-lg active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                Update
            </button>
        </div>
    </form>
</div>
