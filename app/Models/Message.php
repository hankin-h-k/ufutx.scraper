<?php

namespace App\Models;

class Message extends Base
{
    //
    protected $fillable = ['phone', 'code', 'message', 'ip', 'confirmed'];
}
