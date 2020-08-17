<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
	use SoftDeletes;

    protected $fillable = ['name', 'order'];
    protected $dates = ['deleted_at'];
    
    public function chime() {
        return $this->belongsTo(Chime::class);
    }

    public function questions() {
        return $this->hasMany(Question::class);
    }
}
