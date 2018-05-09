<?php

namespace App\Models;

use App\Models\Base;
use Ufutx\LaravelComment\Commentable;

class Book extends Base
{
    use Commentable; 
    protected $canBeRated = true;
    protected $mustBeApproved = false;
    protected $fillable = [];
    protected $guarded  = [];
    //absolete
    public function library_book(){
        return $this->hasOne(LibraryBook::class);
    }

    public function book_libraries(){
        return $this->hasMany(LibraryBook::class);
    }
    
    public function borrows(){
        return $this->hasMany(Borrow::class);
    }

    public function sort(){
        return $this->belongsTo(Sort::class, 'class_id', 'id');
    }

}
