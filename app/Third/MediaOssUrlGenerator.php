<?php namespace App\Third;
use Spatie\MediaLibrary\UrlGenerator\BaseUrlGenerator;
use Illuminate\Support\Facades\Storage;

/**
 * @author: wanghui
 * @date: 2017/4/10 下午8:25
 * @email: hank.huiwang@gmail.com
 */

class MediaOssUrlGenerator extends BaseUrlGenerator {
    /**
     * Get the url for the profile of a media item.
     *
     * @return string
     */
    public function getUrl() : string
    {
        return Storage::disk('oss')->url($this->getPathRelativeToRoot());
    }
}