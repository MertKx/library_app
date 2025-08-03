<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Önce book_store tablosundaki foreign key'leri kaldır
        if (Schema::hasTable('book_store')) {
            Schema::table('book_store', function (Blueprint $table) {
                $table->dropForeign(['book_id']);
                $table->dropForeign(['store_id']);
            });
            Schema::dropIfExists('book_store');
        }
        
        // Sonra books tablosunu yeniden oluştur
        Schema::dropIfExists('books');
        
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('book_name');
            $table->foreignId('author_id')->constrained('authors')->onDelete('cascade');
            $table->string('isbn');
            $table->string('cover_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
