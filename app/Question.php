<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    protected $fillable = ['text', 'order', 'question_info', 'anonymous', 'folder_id'];
    protected $dates = ['deleted_at'];
     
    protected $casts = [
        'question_info' => 'array',
    ];
    public function folder() {
        return $this->belongsTo(Folder::class);
    }

    public function sessions() {
        return $this->hasMany(Session::class);
    }

    public function current_session() {
        return $this->belongsTo('\App\Session', 'current_session_id');
    }
}
