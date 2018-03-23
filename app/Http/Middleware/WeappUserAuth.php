<?php

namespace App\Http\Middleware;

use Closure;

class WeappUserAuth
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
        config(['jwt.user' => '\App\Models\UserOauth']);    //用于指定特定model
        config(['auth.providers.users.model' => \App\Models\UserOauth::class]);//就是他们了
        return $next($request);
    }
}
