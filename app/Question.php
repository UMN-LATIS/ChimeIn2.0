<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['text', 'order', 'question_info'];
    public function folder() {
        return $this->belongsTo(Folder::class);
    }

    public function sessions() {
        return $this->hasMany(Session::class);
    }
}
