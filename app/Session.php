<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $fillable = ['in_progress', 'chime_id'];
    public function question() {
        return $this->belongsTo(Question::class);
    }
    public function chime() {
        return $this->belongsTo(Chime::class);
    }

    public function responses() {
        return $this->hasMany(Response::class);
    }
}
