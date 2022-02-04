<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Yadahan\AuthenticationLog\AuthenticationLogable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use AuthenticationLogable;
    use \Lab404\Impersonate\Models\Impersonate;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'permission_number', 'umndid', 'userType', 'guest_user', 'global_admin'
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

User::deleting(function($user) {
    $chimes = $user->chimes()->get();

    foreach($chimes as $c) {
        $c->detach($user);
    }
});
