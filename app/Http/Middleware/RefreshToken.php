<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Sean Tymon <tymon148@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use App\Traits\CreateJsonResponseData;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Middleware\BaseMiddleware;


class RefreshToken extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $response = $next($request);

        try {
            $newToken = $this->auth->setRequest($request)->parseToken()->refresh();
        } catch (TokenExpiredException $e) {
            $this->respond('tymon.jwt.expired', 'token_expired', $e->getStatusCode(), [$e]);
            return CreateJsonResponseData::createJsonData(false,[],ApiException::TOKEN_EXPIRED,'您的登录已过期')->setStatusCode($e->getStatusCode());
        } catch (JWTException $e) {
            $this->respond('tymon.jwt.invalid', 'token_invalid', $e->getStatusCode(), [$e]);
            return CreateJsonResponseData::createJsonData(false,[],ApiException::TOKEN_INVALID,'您的登录已过期')->setStatusCode($e->getStatusCode());
        }

        // send the refreshed token back to the client
        $response->headers->set('Authorization', 'Bearer '.$newToken);

        return $response;
    }
}
