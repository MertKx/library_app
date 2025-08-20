<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
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

    public function stores()
    {
        return $this->belongsToMany(Store::class);
    }
}
