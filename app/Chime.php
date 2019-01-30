<?php

namespace App;
use DB;
use Sessions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Chime extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'access_code', 'require_login', 'students_can_view'];
    protected $dates = ['deleted_at'];

    public function folders() {
        return $this->hasMany(Folder::class);
    }
    
    public function users() {
        return $this->belongsToMany(User::class)
            ->withPivot('permission_number')
            ->withTimestamps();
    }

    public function sessions() {

        $sessions = DB::table('sessions')->join('questions', 'sessions.question_id', '=', 'questions.id')->join('folders', 'questions.folder_id', '=', 'folders.id')->join('chimes', 'folders.chime_id', '=', 'chimes.id')->where('chimes.id', $this->id)->select("sessions.*")->get();

        $sessionModels= \App\Session::hydrate($sessions->toArray()); 
        return $sessionModels;
    }
}

// Chime::deleting(function($chime) {
//     $users = $chime->users()->get();

//     foreach($users as $u) {
//         $u->chimes()->detach($chime);
//     }
// });