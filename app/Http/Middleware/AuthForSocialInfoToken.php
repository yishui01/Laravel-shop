<?php

namespace App\Http\Middleware;

use Closure;

class AuthForSocialInfoToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        config(['auth.providers.users.model' => \App\Models\SocialInfo::class ]);
        return $next($request);
    }
}
