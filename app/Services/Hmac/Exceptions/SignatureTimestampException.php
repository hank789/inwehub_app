<?php namespace App\Services\Hmac\Exceptions;

class SignatureTimestampException extends SignatureException
{
    protected $code = 40002;

    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, $this->code, $previous);
    }

}
