<?php namespace App\Services;
/**
 * @author: wanghui
 * @date: 2018/9/20 下午7:35
 * @email:    hank.HuiWang@gmail.com
 */

/**
 * Class QcloudService
 * http://nlp.qq.com/help.cgi?topic=api#
 * @package App\Services
 */
class QcloudService {
    protected static $instance = null;

    protected $service;

    public function __construct()
    {
        $config = array(
            'SecretId'       => 'AKIDFbpxw44E2d3d5Ox1OdqtkQuCKZgjt4B6',
            'SecretKey'      => 'sX0MemmfrulQt9D4mLHC0WsUxMgv5732',
            'RequestMethod'  => 'POST',
            'DefaultRegion'  => 'gz');
        $this->service = \QcloudApi::load(\QcloudApi::MODULE_WENZHI,$config);
    }

    /**
     * @return QcloudService
     */
    public static function instance(){
        if(!self::$instance){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function textKeywords($title,$content) {
        $package = [
            "title"=>$title,
            "content"=>$content,
            'channel' => 'CHnews_news_tech'
        ];
        $a = $this->service->TextKeywords($package);
        if ($a === false) {
            $error = $this->service->getError();
            echo "Error code:" . $error->getCode() . ".\n";
            echo "message:" . $error->getMessage() . ".\n";
            echo "ext:" . var_export($error->getExt(), true) . ".\n";
            return false;
        } else {
            var_dump($a);
            return array_column($a['keywords'],'keyword');
        }
    }

    public function lexicalAnalysis($text,$type=1) {
        $package = [
            "text"=>$text,
            "code"=>2097152,
            "type"=>$type
        ];
        $a = $this->service->LexicalAnalysis($package);
        if ($a === false) {
            $error = $this->service->getError();
            echo "Error code:" . $error->getCode() . ".\n";
            echo "message:" . $error->getMessage() . ".\n";
            echo "ext:" . var_export($error->getExt(), true) . ".\n";
        } else {
            var_dump($a);
        }
    }

}