<div>
    {{-- In work, do what you enjoy. --}}

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold">Kitaplarım</h1>
        @guest
            <a href="/welcome" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded shadow transition">Giriş Yap</a>
        @endguest
    </div>
</div>
