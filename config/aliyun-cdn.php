<?php
/**
 * Created by PhpStorm.
 * User: birjemin
 * Date: 23/07/2018
 * Time: 11:41
 */

return [
    /**
     * aliyun access key id
     */
    'accessKeyId'      => env('OSS_ACCESS_KEY_ID', 'accessKeyId'),

    /**
     * aliyun access secret
     */
    'accessSecret'     => env('OSS_ACCESS_KEY_SECRET', 'accessSecret'),

    /**
     * type => [GET, POST]
     */
    'type'             => 'GET', //GET POST

    /**
     * format => [XML, POST]
     */
    'format'           => 'JSON', //XML JSON

    /**
     * default
     */
    'signatureMethod'  => 'HMAC-SHA1',

    /**
     * version default
     */
    'version'          => '2018-05-10',

    /**
     * signature version default
     */
    'signatureVersion' => '1.0',
];