<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LTI13ResourceLink extends Model
{
    protected $table = 'lti13_resource_links';
    protected $casts = [
        'launch_data' => 'array'
    ];

    public function deployment() {
        return $this->belongsTo(LTI13Deployment::class);
    }

    public function folder() {
        return $this->hasOne(Folder::class,"lti13_resource_link_id");
    }

    public function chime() {
        return $this->hasOne(Chime::class,"lti13_resource_link_id");
    }
}
