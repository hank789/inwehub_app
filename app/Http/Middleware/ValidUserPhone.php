<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use App\Traits\CreateJsonResponseData;
use Illuminate\Contracts\Auth\Guard;
use Closure;

class ValidUserPhone
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($this->auth->check() && !$this->auth->user()->mobile){
            return CreateJsonResponseData::createJsonData(false,[],ApiException::USER_NEED_VALID_PHONE,ApiException::$errorMessages[ApiException::USER_NEED_VALID_PHONE]);
        }

        return $next($request);
    }
}
