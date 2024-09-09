<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LimitJsonSize
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?int $maxSize = 10240): Response
    {
        $requestSize = strlen($request->getContent());

        if ($request->isJson() && $requestSize > $maxSize) {
            return response()->json([
                'message' => 'The JSON data must not be larger than'
                    . ($maxSize / 1024) . 'KB.'
            ], 413);
        }

        return $next($request);
    }
}
