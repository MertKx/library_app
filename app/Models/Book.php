<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    // Kitap modelinde toplu atamaya izin verilen alanlar
    protected $fillable = [
        'book_name',
        'author',
        'isbn',
        'cover_image',
    ];
}
