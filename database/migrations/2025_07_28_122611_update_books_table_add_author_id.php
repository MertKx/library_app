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
        // Bu migration artık kullanılmıyor, fix_books_table_structure migration'ı kullanılıyor
        // Schema::table('books', function (Blueprint $table) {
        //     if (Schema::hasColumn('books', 'author')) {
        //         $table->dropColumn('author');
        //     }
            
        //     if (!Schema::hasColumn('books', 'author_id')) {
        //         $table->foreignId('author_id')->constrained('authors')->onDelete('cascade');
        //     }
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // Schema::table('books', function (Blueprint $table) {
        //     if (Schema::hasColumn('books', 'author_id')) {
        //         $table->dropForeign(['author_id']);
        //         $table->dropColumn('author_id');
        //     }
        //     if (!Schema::hasColumn('books', 'author')) {
        //         $table->string('author');
        //     }
        // });
    }
};
