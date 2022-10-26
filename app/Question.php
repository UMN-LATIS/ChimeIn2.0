<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = ['text', 'order', 'question_info', 'anonymous', 'folder_id', "allow_multiple"];
    protected $dates = ['deleted_at'];

    protected $casts = [
        'question_info' => 'array',
        'anonymous' => 'boolean',
        'allow_multiple' => 'boolean'
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
