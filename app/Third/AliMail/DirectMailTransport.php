<?php namespace App\Third\AliMail;
/**
 * @author: wanghui
 * @date: 2019/1/14 下午2:50
 * @email:    hank.HuiWang@gmail.com
 */

use GuzzleHttp\ClientInterface;
use Illuminate\Mail\Transport\Transport;
use Swift_Mime_SimpleMessage;
/**
 * Class Transport.
 *
 * @author overtrue <i@overtrue.me>
 */
class DirectMailTransport extends Transport
{
    /**
     * Guzzle client instance.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;
    /**
     * @var string
     */
    protected $key;

    private $accessSecret;
    /**
     * @var array
     */
    protected $options = [];
    /**
     * @var string
     */
    protected $url = 'https://dm.aliyuncs.com';

    /**
     * Create a new SparkPost transport instance.
     *
     * @param \GuzzleHttp\ClientInterface $client
     * @param string                      $key
     * @param array                       $options
     */
    public function __construct(ClientInterface $client, $key, $options = [])
    {
        $this->key = $key;
        $this->accessSecret = $options['accessSecret'];
        $this->client = $client;
        $this->options = $options;
    }
    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param Swift_Mime_SimpleMessage $message
     * @param string[]                 $failedRecipients An array of failures by-reference
     *
     * @return int
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);
        $to = $this->getTo($message);
        $message->setBcc([]);
        $this->client->post($this->url,$this->payload($message, $to));
        $this->sendPerformed($message);
        return $this->numberOfRecipients($message);
    }
    /**
     * Get the HTTP payload for sending the Mailgun message.
     *
     * @param \Swift_Mime_SimpleMessage $message
     * @param string                    $to
     *
     * @return array
     */
    protected function payload(Swift_Mime_SimpleMessage $message, $to)
    {
        $from = $message->getFrom();
        if (is_array($from)) {
            foreach ($from as $account=>$name) {
                $accountName = $account;
                $fromAlias = $name;
            }
        } else {
            $accountName = $from;
            $fromAlias = array_get($this->options, 'from_alias');
        }
        $parameters = [
            'Action' => 'SingleSendMail',
            'AccountName' => $accountName,
            'ReplyToAddress' => 'false',
            'AddressType' => array_get($this->options, 'address_type', 1),
            'ToAddress' => $to,
            'FromAlias' => $fromAlias,
            'Subject' => $message->getSubject(),
            'HtmlBody' => $message->getBody(),
            'ClickTrace' => array_get($this->options, 'click_trace', 0),
            'Format' => 'json',
            'Version' => array_get($this->options, 'version', '2015-11-23'),
            'AccessKeyId' => $this->getKey(),
            'Timestamp' => date('Y-m-d\TH:i:s\Z', strtotime('-8 hours')),
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureVersion' => '1.0',
            'SignatureNonce' => \uniqid(),
            'RegionId' => \array_get($this->options, 'region_id')
        ];
        ksort($parameters);
        //var_dump($parameters);
        $parameters['Signature'] = $this->makeSignature($parameters);
        return ['form_params'=>$parameters];
    }
    /**
     * @param array $parameters
     *
     * @return string
     */
    protected function makeSignature(array $parameters)
    {
        $signString = 'POST&%2F&'.$this->percentEncode(substr($this->encodeUrl($parameters),1));
        return base64_encode(hash_hmac('sha1', $signString, $this->accessSecret. '&', true));
    }

    protected function percentEncode($str) {
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }

    /**
     * encode url
     * @param string $str
     *
     * @return string
     */
    private function encodeUrl($parameters)
    {
        $str = '';
        foreach ($parameters as $key=>$value) {
            $str .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
        }
        return $str;
    }

    /**
     * Get the "to" payload field for the API request.
     *
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return string
     */
    protected function getTo(Swift_Mime_SimpleMessage $message)
    {
        return collect($this->allContacts($message))->map(function ($display, $address) {
            return $display ? $display." <{$address}>" : $address;
        })->values()->implode(',');
    }
    /**
     * Get the transmission ID from the response.
     *
     * @param \GuzzleHttp\Psr7\Response $response
     *
     * @return string
     */
    protected function getTransmissionId($response)
    {
        return object_get(
            json_decode($response->getBody()->getContents()), 'RequestId'
        );
    }
    /**
     * Get all of the contacts for the message.
     *
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return array
     */
    protected function allContacts(Swift_Mime_SimpleMessage $message)
    {
        return array_merge(
            (array) $message->getTo(), (array) $message->getCc(), (array) $message->getBcc()
        );
    }
    /**
     * Get the API key being used by the transport.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
    /**
     * Set the API key being used by the transport.
     *
     * @param string $key
     *
     * @return string
     */
    public function setKey($key)
    {
        return $this->key = $key;
    }

}