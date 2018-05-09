<?php

namespace App\Models;


class Library extends Base 
{
    //
    public function libraryBooks(){
        return $this->hasMany(LibraryBook::class);
    }

    public function sorts(){
        return $this->hasMany(Sort::class);
    }

    public function members(){
        return $this->hasMany(Member::class);
    }

    public function borrows(){
        return $this->hasMany(Borrow::class);
    }
}
