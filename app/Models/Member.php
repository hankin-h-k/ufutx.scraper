<?php

namespace App\Models;

use App\Models\Base;

class Member extends Base 
{
    //
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function library(){
        return $this->belongsTo(Library::class);
    }
}
