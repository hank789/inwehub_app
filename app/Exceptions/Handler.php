<?php

namespace App\Exceptions;

use App\Traits\CreateJsonResponseData;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
        ApiValidationException::class,
        ApiException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        $dev_should_reports = [
            ApiValidationException::class,
            ApiException::class,
        ];
        if(config('app.dev') != 'production'){
            foreach($dev_should_reports as $dev_should_report){
                if($exception instanceof $dev_should_report){
                    app('sentry')->captureException($exception);
                }
            }
        }
        if ($this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {

        if ($exception instanceof TokenExpiredException) {
            return CreateJsonResponseData::createJsonData(false,[],ApiException::TOKEN_EXPIRED,'token已失效')->setStatusCode($exception->getStatusCode());
        } else if ($exception instanceof TokenInvalidException) {
            return CreateJsonResponseData::createJsonData(false,[],ApiException::TOKEN_INVALID,'token无效')->setStatusCode($exception->getStatusCode());
        }

        if($exception instanceof ApiException){
            return CreateJsonResponseData::createJsonData(false,[],$exception->getCode(),$exception->getMessage());
        }

        if($exception instanceof ApiValidationException){
            $res_data = (array)$exception->getResponse()->getData();
            $err_msg = '';
            foreach($res_data as $msg){
                $err_msg .= $msg[0]."\n";
            }
            $err_msg = trim($err_msg,"\n");
            return CreateJsonResponseData::createJsonData(false,$res_data,$exception->getCode(), $err_msg);
        }
        if($exception instanceof HttpException){
            return CreateJsonResponseData::createJsonData(false,[],$exception->getStatusCode(),$exception->getMessage());
        }

        if($request->is('api/*')){
            return CreateJsonResponseData::createJsonData(false,[],$exception->getCode(),'出错了,请稍后再试~');
        }

        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
