<?php
/**
 * Created by PhpStorm.
 * User: birjemin
 * Date: 23/07/2018
 * Time: 17:19
 */

namespace App\Third\AliCdn\Exception;

/**
 * Class CdnException
 * @package Birjemin\AliyunCdn\Exception
 */
class CdnException extends \RuntimeException
{
    /**
     * Response body.
     *
     * @var array
     */
    public $body;

    /**
     * Constructor.
     *
     * @param string $message
     * @param array  $body
     */
    public function __construct($message, $body)
    {
        parent::__construct($message, -1);
        $this->body = $body;
    }
}
