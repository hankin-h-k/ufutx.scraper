<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wechat extends Model
{
    protected $fillable = [];
    protected $guarded  = [];
    /*
     * user info
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
