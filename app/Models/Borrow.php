<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Borrow extends Model
{
    use SoftDeletes;

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function library(){
        return $this->belongsTo(Library::class);
    }

    public function book(){
        return $this->belongsTo(Book::class);
    }
}
