<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Ufutx\LaravelComment\CanComment;
use Overtrue\LaravelFollow\Traits\CanFollow;
use Overtrue\LaravelFollow\Traits\CanBeFollowed;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;
    use CanComment;
    use CanFollow, CanBeFollowed;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'mobile', 'email', 'password', 'isAdmin'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /* 
     * wechat info
     */
    public function wechat()
    {
        return $this->hasOne(Wechat::class);
    }

    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }

    public function libraries()
    {
        return $this->hasMany(Library::class);
    }

    public function isAdmin() { 
        return $this->isAdmin; 
    }
}
