<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'permission_number', 'umndid', 'userType'
        // 'enrolled_courses'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function chimes() {
        return $this->belongsToMany(Chime::class)
            ->withPivot('permission_number')
            ->withTimestamps();
    }

    public function responses() {
        return $this->hasMany(Response::class);
    }
}

User::creating(function($user) {
    if ($user->userType == 'faculty') {
        $user->permission_number = 300;
    }
});

User::deleting(function($user) {
    $chimes = $user->chimes()->get();

    foreach($chimes as $c) {
        $c->detach();
    }
});
