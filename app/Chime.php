<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chime extends Model
{
    protected $fillable = ['name', 'access_code'];

    public function folders() {
        return $this->hasMany(Folder::class);
    }

    public function sessions() {
        return $this->hasMany(Session::class);
    }
    
    public function users() {
        return $this->belongsToMany(User::class)
            ->withPivot('permission_number')
            ->withTimestamps();
    }
}

Chime::deleting(function($chime) {
    $users = $chime->users()->get();

    foreach($users as $u) {
        $u->chimes()->detach($chime);
    }
});