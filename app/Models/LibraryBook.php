<?php

namespace App\Models;

use App\Models\Base;

class LibraryBook extends Base
{
    public function book(){
        return $this->belongsTo(Book::class);
    }

    public function sorts(){
        return $this->hasMany(Sort::class, 'library_id', 'library_id');
    }

    public function library(){
        return $this->belongsTo(Library::class);
    }
}
