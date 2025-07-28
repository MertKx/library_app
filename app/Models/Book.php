<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    // Kitap modelinde toplu atamaya izin verilen alanlar
    protected $fillable = [
        'book_name',
        'author_id',
        'isbn',
        'cover_image',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}
