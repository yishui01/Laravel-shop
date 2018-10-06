<?php

namespace App\Http\Middleware;

use App\Exceptions\InvalidRequestException;
use Closure;

class RandomDropSeckillRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $percent)
    {
        if (random_int(0, 100) < (int)$percent) {
            throw new InvalidRequestException('参与的用户过多，请稍后再试', 403);
        }

        return $next($request);
    }
}
