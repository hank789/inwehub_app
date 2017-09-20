<?php
/**
 * Created by jacob.
 * Date: 2016/5/19 0019
 * Time: 下午 17:07
 */

namespace App\Third\AliOss;

use Jacobcyl\AliOSS\AliOssAdapter as ParentAliOssAdapter;

class AliOssAdapter extends ParentAliOssAdapter
{

    /**
     * @param $path
     *
     * @return string
     */
    public function getUrl( $path )
    {
        return ( $this->ssl ? 'https://' : 'http://' ) . ( $this->isCname ? ( $this->cdnDomain == '' ? $this->endpoint : $this->cdnDomain ) : $this->bucket . '.' . $this->endPoint ) . '/' . ltrim($path, '/');
    }

}
