<?php namespace App\Traits;
use App\Exceptions\ApiException;
use App\Jobs\UploadFile;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @author: wanghui
 * @date: 2017/11/13 下午6:53
 * @email: hank.huiwang@gmail.com
 */

trait SubmitSubmission
{
    /**
     * Creates a unique slug for the submission.
     *
     * @param string $title
     *
     * @return string
     */
    protected function slug($title)
    {
        $slug = app('pinyin')->abbr(strip_tags($title));
        if (empty($slug)) {
            $slug = 1;
        }
        if (strlen($slug) > 50) {
            $slug = substr($slug,0,50);
        }

        $slugNumber = 1;
        $newSlug = $slug;

        while (true) {
            $submission = Submission::withTrashed()->where('slug', $newSlug)->first();
            if (!$submission) return $newSlug;
            $newSlug = $slug.'-'.$slugNumber;
            $slugNumber++;
        }

        return $newSlug;
    }

    /**
     * @param  Request instance
     *
     * @return json data
     */
    protected function linkSubmission(Request $request)
    {
        $apiURL = 'https://midd.voten.co/link-submission?url='.urlencode($request->url);

        $info = json_decode(file_get_contents($apiURL));

        return [
            'url'           => $info->url,
            'title'         => $info->title,
            'description'   => $info->description,
            'type'          => $info->type,
            'embed'         => $info->embed,
            'img'           => $info->img,
            'thumbnail'     => $info->thumbnail,
            'providerName'  => $info->providerName,
            'publishedTime' => $info->publishedTime,
            'domain'        => $info->domain,
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    protected function textSubmission(Request $request)
    {
        $photos = $request->input('photos');
        $list = [];
        if ($photos) {
            foreach ($photos as $base64) {
                $url = explode(';',$base64);
                if(count($url) <=1){
                    $parse_url = parse_url($base64);
                    //非本地地址，存储到本地
                    if (isset($parse_url['host']) && !in_array($parse_url['host'],['cdnread.ywhub.com','cdn.inwehub.com','inwehub-pro.oss-cn-zhangjiakou.aliyuncs.com','intervapp-test.oss-cn-zhangjiakou.aliyuncs.com'])) {
                        $file_name = 'submissions/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.jpeg';
                        dispatch((new UploadFile($file_name,base64_encode(file_get_contents_curl($base64)))));
                        //Storage::disk('oss')->put($file_name,file_get_contents($base64));
                        $img_url = Storage::disk('oss')->url($file_name);
                        $list[] = $img_url;
                    } elseif(isset($parse_url['host'])) {
                        $list[] = $base64;
                    }
                    continue;
                }
                $url_type = explode('/',$url[0]);
                $file_name = 'submissions/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$url_type[1];
                dispatch((new UploadFile($file_name,(substr($url[1],6)))));
                //Storage::disk('oss')->put($file_name,base64_decode(substr($url[1],6)));
                $img_url = Storage::disk('oss')->url($file_name);
                $list[] = $img_url;
            }
        }
        return ['img'=>$list];
    }

    /**
     * whether or not the title has already been posted.
     *
     * @return bool
     */
    protected function isDuplicateTitle($title, $category)
    {
        return Submission::withTrashed()->where('title', $title)->where('category_name', $category)->exists();
    }

}
