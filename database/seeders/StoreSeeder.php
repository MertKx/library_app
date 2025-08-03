<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Store;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = [
            ['name' => 'D&R', 'slug' => 'dr'],
            ['name' => 'İdefix', 'slug' => 'idefix'],
            ['name' => 'Kitap Yurdu', 'slug' => 'kitap-yurdu'],
            ['name' => 'Trendyol', 'slug' => 'trendyol'],
        ];

        foreach ($stores as $store) {
            Store::firstOrCreate(['slug' => $store['slug']], $store);
        }
    }
}
