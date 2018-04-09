<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $fillable = ['name'];
    public function chime() {
        return $this->belongsTo(Chime::class);
    }

    public function questions() {
        return $this->hasMany(Question::class);
    }
}
