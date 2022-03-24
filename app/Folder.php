<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
	use SoftDeletes;

    protected $fillable = ['name', 'order'];
    protected $dates = ['deleted_at'];
    protected $withCount = ['questions'];
    
    public function chime() {
        return $this->belongsTo(Chime::class);
    }

    public function questions() {
        return $this->hasMany(Question::class);
    }

    public function lti13_resource_link() {
        return $this->belongsTo(LTI13ResourceLink::class);
    }
}
