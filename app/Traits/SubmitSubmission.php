<?php namespace App\Traits;
use App\Exceptions\ApiException;
use App\Models\Readhub\Submission;
use App\Models\Readhub\SubmissionUpvotes;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * @author: wanghui
 * @date: 2017/11/13 下午6:53
 * @email: wanghui@yonglibao.com
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
        $slug = str_slug($title);
        if(empty($slug)) {
            $slug = app('pinyin')->abbr($title);
        }
        $submissions = Submission::withTrashed()->where('slug', 'like', $slug.'%')->get();

        if (!$submissions->contains('slug', $slug)) {
            return $slug;
        }

        $slugNumber = 1;
        $newSlug = $slug;

        while ($submissions->contains('slug', $newSlug)) {
            $newSlug = $slug.'-'.$slugNumber;
            $slugNumber++;
        }

        return $newSlug;
    }

    /**
     * Up-votes on submission.
     *
     * @param collection $user
     * @param int        $submission_id
     *
     * @return void
     */
    protected function firstVote($user, $submission_id)
    {
        SubmissionUpvotes::create([
            'user_id' => $user->id,
            'ip_address' => getRequestIpAddress(),
            'submission_id' => $submission_id
        ]);
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
     * @param Illuminate\Http\Request $request
     *
     * @return array
     */
    protected function imgSubmission(Request $request)
    {
        $photo = Photo::where('id', $request->input('photos')[0])->firstOrFail();

        return [
            'path'           => $photo->path,
            'thumbnail_path' => $photo->thumbnail_path,
            'album'          => (count($request->input('photos')) > 1),
        ];
    }

    /**
     * Animated gif submissions: The uploaded .gif file should get converted
     * to .mp4 format to save bandwitch for both server and user. This way
     * we get to use a pretty HTML5 player for playing .gif files.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return array
     */
    protected function gifSubmission(Request $request)
    {
        $this->validate($request, [
            'gif' => 'required|mimes:gif|max:51200',
        ]);

        $file = $request->file('gif');

        $filename = time().str_random(7);

        $file->storeAs('submissions/gif', $filename.'.gif', 'local');

        FFMpeg::fromDisk('local')
            ->open('submissions/gif/'.$filename.'.gif')
            // mp4
            ->export()
            ->toDisk('local')
            ->inFormat((new \FFMpeg\Format\Video\X264())->setAdditionalParameters([
                '-movflags', 'faststart',
                '-pix_fmt', 'yuv420p',
                '-preset', 'veryslow',
                '-b:v', '500k',
            ]))
            ->save('submissions/gif/'.$filename.'.mp4')
            // thumbnail
            ->getFrameFromSeconds(1)
            ->export()
            ->toDisk('local')
            ->save('submissions/gif/'.$filename.'.jpg');

        // get the uploaded mp4 and move it to the ftp
        $mp4 = Storage::disk('local')->get('submissions/gif/'.$filename.'.mp4');
        Storage::disk('oss')->put('submissions/gif/'.$filename.'.mp4', $mp4);

        // get the uploaded jpg and move it to the ftp
        $jpg = Storage::disk('local')->get('submissions/gif/'.$filename.'.jpg');
        Storage::disk('oss')->put('submissions/gif/'.$filename.'.jpg', $jpg);

        // delete temp files from local storage
        Storage::disk('local')->delete([
            'submissions/gif/'.$filename.'.jpg',
            'submissions/gif/'.$filename.'.gif',
            'submissions/gif/'.$filename.'.mp4',
        ]);

        return [
            'mp4_path'       => Storage::url('submissions/gif/'.$filename.'.mp4'),
            'thumbnail_path' => Storage::url('submissions/gif/'.$filename.'.jpg'),
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
                        Storage::disk('oss')->put($file_name,file_get_contents($base64));
                        $img_url = Storage::disk('oss')->url($file_name);
                        $list[] = $img_url;
                    }
                    continue;
                }
                $url_type = explode('/',$url[0]);
                $file_name = 'submissions/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$url_type[1];
                Storage::disk('oss')->put($file_name,base64_decode(substr($url[1],6)));
                $img_url = Storage::disk('oss')->url($file_name);
                $list[] = $img_url;
            }
        }
        return ['photos'=>$list];
    }

    /**
     * Fetches the title from an external URL.
     *
     * @param  $url
     *
     * @return string title
     */
    protected function getTitle($url)
    {
        //$apiURL = 'https://midd.voten.co/embed/title?url='.$url;

        try {
            //$title = file_get_contents($apiURL);
            $title = getUrlTitle($url);
        } catch (\Exception $exception) {
            throw new ApiException(ApiException::ARTICLE_GET_URL_TITLE_ERROR);
        }

        return $title;
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

    /**
     * has user upvoted for this submission before?
     *
     * @return bool
     */
    protected function hasUserUpvotedSubmission($user_id, $submission_id)
    {
        return DB::table('submission_upvotes')->where('user_id', $user_id)->where('submission_id', $submission_id)->exists();
    }
}
