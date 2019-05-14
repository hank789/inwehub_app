<?php namespace App\Services\Hmac\Exceptions;

class SignatureSignatureException extends SignatureException
{
    protected $code = 40001;

    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, $this->code, $previous);
    }
}
