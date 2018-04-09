<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    protected $fillable = ['response_info', 'user_id'];
    
    public function session() {
        return $this->belongsTo(Session::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
