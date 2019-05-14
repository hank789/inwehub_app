<?php namespace App\Services\Hmac\Exceptions;

class SignatureVersionException extends SignatureException
{
    protected $code = 40003;

    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, $this->code, $previous);
    }
}
