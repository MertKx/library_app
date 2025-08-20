<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_name',
        'author',
        'isbn',
        'cover_image',
    ];

    public $timestamps = true;
}
