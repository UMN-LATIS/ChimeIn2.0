<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Session extends Model
{
    use SoftDeletes;
    protected $fillable = [];
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
