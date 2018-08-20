<?php namespace App\Services;
use HeXiangHui\BosonNLP\BosonNLP;

/**
 * @author: wanghui
 * @date: 2018/8/17 下午3:22
 * @email: wanghui@yonglibao.com
 * https://bosonnlp.com/
 */

class BosonNLPService {
    protected static $instance = null;

    /**
     * @return BosonNLP
     */
    public static function instance(){
        if(!self::$instance){
            self::$instance = new BosonNLP(config('services.boson.api_key'));
        }
        return self::$instance;
    }

}