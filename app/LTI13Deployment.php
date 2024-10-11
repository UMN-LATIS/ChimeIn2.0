<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LTI13Deployment extends Model
{
    protected $table = 'lti13_deployments';

    public function issuer()
    {
        return $this->belongsTo(LTI13Issuer::class);
    }

    public function resourceLinks()
    {
        return $this->hasMany(LTI13ResourceLink::class, 'deployment_id');
    }
}
