<?php namespace App\Services\Hmac\Exceptions;

class SignatureKeyException extends SignatureException
{
    protected $code = 40000;

    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, $this->code, $previous);
    }
}
