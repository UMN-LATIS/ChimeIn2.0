<?php

namespace App\Library;
use Illuminate\Support\Facades\Cookie;
use Packback\Lti1p3\Interfaces\Cookie as Lti1p3Cookie;

class LTI13Cookie implements Lti1p3Cookie
{
    public function getCookie($name)
    {
        return Cookie::get($name, false);
    }

    public function setCookie($name, $value, $exp = 3600, $options = []): self
    {
        Cookie::queue($name, $value, $exp);
        return $this;
    }
}