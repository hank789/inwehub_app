<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use App\Traits\CreateJsonResponseData;
use Illuminate\Contracts\Auth\Guard;
use Closure;

class BanUserCheck
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
        if($this->auth->check() && $this->auth->user()->status === -1){
            return CreateJsonResponseData::createJsonData(false,[],ApiException::USER_NEED_CONFIRM,ApiException::$errorMessages[ApiException::USER_NEED_CONFIRM]);
        }
        if($this->auth->check() && $this->auth->user()->status === 0){
            return CreateJsonResponseData::createJsonData(false,[],ApiException::USER_NEED_CONFIRM,ApiException::$errorMessages[ApiException::USER_NEED_CONFIRM]);
        }
        return $next($request);
    }
}
