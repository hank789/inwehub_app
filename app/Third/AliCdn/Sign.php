<?php
/**
 * Created by PhpStorm.
 * User: birjemin
 * Date: 23/07/2018
 * Time: 11:35
 */
namespace App\Third\AliCdn;


/**
 * Class Sign
 * https://cdn.aliyuncs.com/?
 * Format=xml (json/xml)
 * &Version=2014-11-11 （2014-11-11）
 * &Signature=Pc5WB8gokVn0xfeu%2FZV%2BiNM1dgI%3D
 * &SignatureMethod=HMAC-SHA1 (HMAC-SHA1)
 * &SignatureNonce=15215528852396 (唯一随机数)
 * &SignatureVersion=1.0 (1.0)
 * &AccessKeyId=key-test
 * &Timestamp=2012-06-01T12:00:00Z (YYYY-MM-DDThh:mm:ssZ)
 */
class Sign
{
    private $accessKeyId      = '';
    private $accessSecret     = '';

    // 参数
    private $params           = [];
    private $type             = 'GET'; //GET POST
    private $format           = 'JSON'; //XML JSON
    private $signatureMethod  = 'HMAC-SHA1';
    private $version          = '2014-11-11';
    private $signatureVersion = '1.0';

    /** @var array 需要特殊处理的参数 */
    private $encodeParams     = [
        '+'   => '%20',
        '*'   => '%2A',
        '%7E' => '~',
    ];

    /**
     * Sign constructor.
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params           = $params;
        $this->type             = config('aliyun-cdn.type', 'GET');
        $this->format           = config('aliyun-cdn.format', 'JSON');
        $this->signatureMethod  = config('aliyun-cdn.signatureMethod', 'HMAC-SHA1');
        $this->version          = config('aliyun-cdn.version', '2014-11-11');
        $this->signatureVersion = config('aliyun-cdn.signatureVersion', '1.0');
        $this->accessKeyId      = config('aliyun-cdn.accessKeyId', '');
        $this->accessSecret     = config('aliyun-cdn.accessSecret', '');
    }

    /**
     * @return array
     */
    public function buildParams()
    {
        $this->params += [
            'SignatureVersion' => $this->signatureVersion,
            'Format'           => $this->format,
            'Timestamp'        => date('Y-m-d\TH:i:s\Z', strtotime('-8 hours')),
            'AccessKeyId'      => $this->accessKeyId,
            'SignatureMethod'  => $this->signatureMethod,
            'Version'          => $this->version,
            'SignatureNonce'   => $this->generateRand(),
        ];
        return $this->params + [
            'Signature' => $this->generateSign()
        ];
    }

    /**
     * generate sign string
     * @return string
     */
    private function generateSign()
    {
        ksort($this->params);
        // GET&/&...
        $str = $this->type . '&%2F&' . $this->parseUrl();
        return base64_encode(hash_hmac('sha1', $str, $this->accessSecret . '&', true));
    }

    /**
     * 组合 url 需要特殊处理
     * @return string
     */
    private function parseUrl()
    {
        return $this->encodeUrl(http_build_query($this->params));
    }

    /**
     * encode url
     * @param string $str
     *
     * @return string
     */
    private function encodeUrl(string $str)
    {
        $str     = urlencode($str);
        foreach ($this->encodeParams as $key => $val) {
            $str = str_replace($key, $val, $str);
        }
        return $str;
    }

    /**
     * 基本上不可能重复了好吧
     * @return string
     */
    private function generateRand()
    {
        return md5(uniqid() . mt_rand(1000, 9999));
    }
}