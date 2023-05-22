<?php

namespace App\Library;
use Illuminate\Support\Facades\Cookie;
use Packback\Lti1p3\Interfaces\ICookie as Lti1p3Cookie;

class LTI13Cookie implements Lti1p3Cookie
{
 public function getCookie(string $name): ?string
    {
        return Cookie::get($name, false);
    }

    public function setCookie(string $name, string $value, $exp = 3600, $options = []): void
    {
        // By default, make the cookie expire within a minute
        Cookie::queue($name, $value, $exp / 60);
    }
}