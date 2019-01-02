<?php namespace App\Third;
use QL\QueryList;

class RssFeed
{
	/** @var int */
	public static $cacheExpire = '1 day';

	/** @var string */
	public static $cacheDir;

	/** @var \SimpleXMLElement */
	protected $xml;


	/**
	 * Loads RSS or Atom feed.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return \SimpleXMLElement
	 * @throws \Exception
	 */
	public static function load($url, $user = null, $pass = null)
	{
		$xml = self::loadXml($url, $user, $pass);
		if ($xml->channel) {
			return self::fromRss($xml);
		} else {
			return self::fromAtom($xml);
		}
	}


	/**
	 * Loads RSS feed.
	 * @param  string  RSS feed URL
	 * @param  string  optional user name
	 * @param  string  optional password
	 * @return \SimpleXMLElement
	 * @throws \Exception
	 */
	public static function loadRss($url, $user = null, $pass = null)
	{
		return self::fromRss(self::loadXml($url, $user, $pass));
	}


	/**
	 * Loads Atom feed.
	 * @param  string  Atom feed URL
	 * @param  string  optional user name
	 * @param  string  optional password
	 * @return \SimpleXMLElement
	 * @throws \Exception
	 */
	public static function loadAtom($url, $user = null, $pass = null)
	{
		return self::fromAtom(self::loadXml($url, $user, $pass));
	}


	private static function fromRss($xml)
	{
		if (!$xml->channel) {
			throw new \Exception('Invalid feed.');
		}
		return $xml;
	}


	private static function fromAtom(\SimpleXMLElement $xml)
	{
		if (!in_array('http://www.w3.org/2005/Atom', $xml->getDocNamespaces(), true)
			&& !in_array('http://purl.org/atom/ns#', $xml->getDocNamespaces(), true)
		) {
			throw new \Exception('Invalid feed.');
		}
		return $xml;
	}


	/**
	 * Returns property value. Do not call directly.
	 * @param  string  tag name
	 * @return \SimpleXMLElement
	 */
	public function __get($name)
	{
		return $this->xml->{$name};
	}


	/**
	 * Sets value of a property. Do not call directly.
	 * @param  string  property name
	 * @param  mixed   property value
	 * @return void
	 */
	public function __set($name, $value)
	{
		throw new \Exception("Cannot assign to a read-only property '$name'.");
	}


	/**
	 * Converts a SimpleXMLElement into an array.
	 * @param  \SimpleXMLElement
	 * @return array
	 */
	public function toArray(\SimpleXMLElement $xml = null)
	{
		if ($xml === null) {
			$xml = $this->xml;
		}

		if (!$xml->children()) {
			return (string) $xml;
		}

		$arr = array();
		foreach ($xml->children() as $tag => $child) {
			if (count($xml->$tag) === 1) {
				$arr[$tag] = $this->toArray($child);
			} else {
				$arr[$tag][] = $this->toArray($child);
			}
		}

		return $arr;
	}


	/**
	 * Load XML from cache or HTTP.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return \SimpleXMLElement
	 * @throws \Exception
	 */
	public static function loadXml($url, $user, $pass)
	{

		if ($data = trim(self::httpRequest($url, $user, $pass))) {

		} else {
			throw new \Exception('Cannot load feed.');
		}
        $invalid_characters = '/[^\x9\xa\x20-\xD7FF\xE000-\xFFFD]/';
        $data = preg_replace($invalid_characters, '', $data );
		return simplexml_load_string($data,'SimpleXMLElement');
	}


	/**
	 * Process HTTP request.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return string|false
	 * @throws \Exception
	 */
	public static function httpRequest($url, $user, $pass)
	{
		$ql = QueryList::getInstance();
		$options = [];
		if (config('app.env') == 'production') {
            $options = ['proxy' => 'socks5h://127.0.0.1:1080'];
        }
		return $ql->get($url,[],$options)->getHtml();
	}
}
