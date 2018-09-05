<?php namespace App\Services;
/**
 * @author: wanghui
 * @date: 2017/4/6 下午11:47
 * @email: hank.huiwang@gmail.com
 */


class Singleton {

    protected static $_classes = array();

    /**
     * @return static
     */
    public static function instance(){
        $class = static::class;
        if (!isset(self::$_classes[$class])) {
            self::$_classes[$class] = new static();
        }
        return self::$_classes[$class];
    }

}