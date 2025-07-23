<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Book;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 5 örnek kitap ekle
        Book::insert([
            [
                'book_name' => 'Yüzüklerin Efendisi',
                'author' => 'J.R.R. Tolkien',
                'isbn' => '9789753425987',
                'cover_image' => 'covers/lotr.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'book_name' => 'Suç ve Ceza',
                'author' => 'Fyodor Dostoyevski',
                'isbn' => '9786053608070',
                'cover_image' => 'covers/sucveceza.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'book_name' => 'Kürk Mantolu Madonna',
                'author' => 'Sabahattin Ali',
                'isbn' => '9789753638028',
                'cover_image' => 'covers/kurkmantolu.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'book_name' => '1984',
                'author' => 'George Orwell',
                'isbn' => '9789750718532',
                'cover_image' => 'covers/1984.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'book_name' => 'Sefiller',
                'author' => 'Victor Hugo',
                'isbn' => '9786053323560',
                'cover_image' => 'covers/sefiller.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
