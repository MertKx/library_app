<x-layouts.app.sidebar title="Library">
    <flux:main>
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
                <h1 class="text-2xl font-bold mb-4 text-gray-800">Bulk Import Books</h1>

                @if (session('status'))
                    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                        {!! session('status') !!}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                        @foreach ($errors->all() as $error)
                            {!! $error !!}
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('bulk-import.upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
                    @csrf

                    <label class="block">
                        <span class="text-gray-800 font-semibold">Select File</span>
                        <div class="relative">
                            <input id="fileInput" type="file" name="file"
                                   accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                                   required
                                   class="block w-full text-sm text-gray-800 border border-gray-400 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-800">
                            <p id="fileName" class="mt-2 text-sm text-gray-800"></p>
                        </div>
                    </label>

                    <button type="submit" class="bg-black hover:bg-gray-800 text-white px-4 py-2 rounded shadow transition">
                        Upload
                    </button>
                </form>
            </div>
        </div>

        <script>
            const fileInput = document.getElementById('fileInput');
            const fileName = document.getElementById('fileName');

            fileInput.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    fileName.textContent = `Selected: ${this.files[0].name}`;
                } else {
                    fileName.textContent = '';
                }
            });

            // Check for import errors from jobs
            @if(session('history_id'))
                const historyId = '{{ session('history_id') }}';
                const checkForErrors = () => {
                    fetch(`{{ route('bulk-import.index') }}?history_id=${historyId}`)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const errorDiv = doc.querySelector('.bg-red-100');
                            
                            if (errorDiv) {
                                location.reload();
                            }
                        });
                };
                
                // Check for errors every 5 seconds
                setInterval(checkForErrors, 5000);
            @endif
        </script>
    </flux:main>
</x-layouts.app.sidebar>
