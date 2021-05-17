<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LTI13ResourceLink extends Model
{

    const LTI_GRADE_MODE_NO_GRADES = "no_grades";
    const LTI_GRADE_MODE_ONE_GRADE = "one_grade";
    const LTI_GRADE_MODE_MULTIPLE_GRADES = "multiple_grades";

    
    protected $table = 'lti13_resource_links';
    protected $casts = [
        'endpoint' => 'array'
    ];

    public function deployment() {
        return $this->belongsTo(LTI13Deployment::class);
    }


    public function chime() {
        return $this->hasOne(Chime::class,"lti13_resource_link_id");
    }


}
