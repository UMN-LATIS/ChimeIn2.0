<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class LTI13Issuer extends Model
{
    protected $table = 'lti13_issuers';
    public function deployments() {
        return $this->hasMany(LTI13Deployment::class, 'issuer_id');
    }
}
