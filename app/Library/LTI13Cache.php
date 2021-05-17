<?php

namespace App\Library;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Packback\Lti1p3\Interfaces\Cache as Lti1p3Cache;


class LTI13Cache implements Lti1p3Cache
{
    public const NONCE_PREFIX = 'nonce_';

    public function getLaunchData($key)
    {
        return Cache::get($key);
    }

    public function cacheLaunchData($key, $jwt_body)
    {
        $duration = Config::get('cache.duration.default');
        Cache::put($key, $jwt_body, $duration);
        return $this;
    }

    public function cacheNonce($nonce)
    {
        $duration = Config::get('cache.duration.default');
        Cache::put(static::NONCE_PREFIX . $nonce, true, $duration);
        return $this;
    }

    public function checkNonce($nonce)
    {
        return Cache::get(static::NONCE_PREFIX . $nonce, false);
    }
}
