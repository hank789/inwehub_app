<?php namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Models\UserOauth;
use App\Models\Weapp\Demand;
use App\Services\RateLimiter;
use App\Third\Weapp\WeApp;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

/**
 * @author: wanghui
 * @date: 2017/4/19 下午7:49
 * @email: hank.huiwang@gmail.com
 */

class WeappController extends Controller
{
    public function getDemandShareLongInfo($id,WeApp $wxxcx)
    {
        $demand = Demand::find($id);
        $cacheKey = 'demand-qrcode';
        $url = RateLimiter::instance()->hGet($cacheKey,$demand->id);
        $page = 'pages/detail/detail';
        $scene = 'demand_id='.$demand->id;
        try {
            if (!$url) {
                $qrcode = $wxxcx->getQRCode()->getQRCodeB($scene,$page);
                $file_name = 'demand/qrcode/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.png';
                Storage::disk('oss')->put($file_name,$qrcode);
                $url = Storage::disk('oss')->url($file_name);
                RateLimiter::instance()->hSet($cacheKey,$demand->id,$url);
            }
        } catch (\Exception $e) {

        }

        $demand_oauth = $demand->user->userOauth->where('auth_type',UserOauth::AUTH_TYPE_WEAPP)->first();
        $data = [
            'publisher_user_id'=>$demand_oauth->user_id,
            'publisher_name'=>$demand->user->name,
            'publisher_avatar'=>$demand_oauth->avatar,
            'publisher_title'=>$demand->user->title,
            'publisher_company'=>$demand->user->company,
            'publisher_email'=>$demand->user->email,
            'publisher_phone' => $demand->user->mobile,
            'title' => $demand->title,
            'address' => $demand->address,
            'salary' => $demand->salary,
            'salary_upper' => $demand->salary_upper?:$demand->salary,
            'salary_type' => $demand->salary_type,
            'industry' => ['value'=>$demand->industry,'text'=>$demand->getIndustryName()],
            'project_cycle' => ['value'=>$demand->project_cycle,'text'=>trans_project_project_cycle($demand->project_cycle)],
            'project_begin_time' => $demand->project_begin_time,
            'description' => $demand->description,
            'expired_at'  => $demand->expired_at,
            'views' => $demand->views,
            'status' => $demand->status,
            'qrcodeUrl' => $url
        ];
        return view('h5::weapp.demandShareLong')->with('demand',$data);
    }

    public function getDemandShareShortInfo($id){
        return view('h5::weapp.demandShareShort');
    }

    public function getProductShareLongInfo($id,Request $request, WeApp $wxxcx){
        $tag = Tag::find($id);
        $oauth_id = $request->input('oauth_id',0);
        if (config('app.env') != 'production') {
            $qrcodeUrlFormat = 'https://cdn.inwehub.com/demand/qrcode/2018/09/153733792816zoTjw.png?x-oss-process=image/resize,w_430,h_430,image/circle,r_300/format,png/watermark,image_cHJvZHVjdC9xcmNvZGUvMjAxOC8xMi8xNTQ1OTc1NDc3WTlMbzZLSi5wbmc=,g_center';
        } else {
            $qrcodeUrl = $this->getProductQrcode($id,$oauth_id,$wxxcx);
            try {
                $qrcodeUrlFormat = weapp_qrcode_replace_logo($qrcodeUrl,$tag->logo);
            } catch (\Exception $e) {
                $qrcodeUrlFormat = $qrcodeUrl;
            }
        }
        return view('h5::weapp.productShareLong')->with('tag',$tag)->with('qrcode',$qrcodeUrlFormat);
    }

    public function getAlbumShareLongInfo($id,Request $request, WeApp $wxxcx){
        $category = Category::find($id);
        $oauth_id = $request->input('oauth_id',0);
        if (config('app.env') != 'production') {
            $qrcodeUrl = 'https://cdn.inwehub.com/demand/qrcode/2018/09/153733792816zoTjw.png?x-oss-process=image/resize,w_430,h_430,image/circle,r_300/format,png/watermark,image_cHJvZHVjdC9xcmNvZGUvMjAxOC8xMi8xNTQ1OTc1NDc3WTlMbzZLSi5wbmc=,g_center';
        } else {
            $qrcodeUrl = $this->getAlbumQrcode($id,$oauth_id,$wxxcx);
        }
        $query = TagCategoryRel::select(['id','tag_id','support_rate'])->where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1);
        $tags = $query->where('category_id',$id)->orderBy('support_rate','desc')->orderBy('updated_at','desc')->take(7)->get();
        $list = [];
        foreach ($tags as $tag) {
            $model = Tag::find($tag->tag_id);
            $info = Tag::getReviewInfo($model->id);
            $list[] = [
                'id' => $tag->id,
                'tag_id' => $model->id,
                'name' => $model->name,
                'logo' => $model->logo,
                'summary' => $model->summary,
                'support_rate' => $tag->support_rate?:0,
                'review_average_rate' => $info['review_average_rate'],
                'advance_desc' => $model->getAdvanceDesc()
            ];
        }
        return view('h5::weapp.albumShareLong')->with('category',$category)->with('tags',$list)->with('qrcode',$qrcodeUrl);
    }

    public function getProductShareShortInfo($id,Request $request, WeApp $wxxcx){
        $tag = Tag::find($id);
        $oauth_id = $request->input('oauth_id',0);
        if (config('app.env') != 'production') {
            $qrcodeUrlFormat = 'https://cdn.inwehub.com/demand/qrcode/2018/09/153733792816zoTjw.png?x-oss-process=image/resize,w_430,h_430,image/circle,r_300/format,png/watermark,image_cHJvZHVjdC9xcmNvZGUvMjAxOC8xMi8xNTQ1OTc1NDc3WTlMbzZLSi5wbmc=,g_center';
        } else {
            $qrcodeUrl = $this->getProductQrcode($id,$oauth_id,$wxxcx);
            try {
                $qrcodeUrlFormat = weapp_qrcode_replace_logo($qrcodeUrl,$tag->logo,true);
            } catch (\Exception $e) {
                $qrcodeUrlFormat = $qrcodeUrl;
            }
        }
        return view('h5::weapp.productShareShort')->with('tag',$tag)->with('qrcode',$qrcodeUrlFormat);
    }

    protected function getProductQrcode($id,$oauth_id, WeApp $wxxcx) {
        $qrcodeUrl = RateLimiter::instance()->hGet('product-qrcode',$id.'_'.$oauth_id);
        if (!$qrcodeUrl) {
            $file_name = 'product/qrcode/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.png';
            $page = 'pages/majorProduct/majorProduct';
            $scene = '='.$id.'='.$oauth_id;
            try {
                $wxxcx->setConfig(config('weapp.appid_ask'),config('weapp.secret_ask'));
                $qrcode = $wxxcx->getQRCode()->getQRCodeB($scene,$page);
                Storage::disk('oss')->put($file_name,$qrcode);
                $qrcodeUrl = Storage::disk('oss')->url($file_name);
                RateLimiter::instance()->hSet('product-qrcode',$id,$qrcodeUrl);
            } catch (\Exception $e) {
                app('sentry')->captureException($e);
            }
        }
        return $qrcodeUrl;
    }

    public function getReviewShareLongInfo($id,Request $request, WeApp $wxxcx){
        $review = Submission::find($id);
        $tag = Tag::find($review->category_id);
        $oauth_id = $request->input('oauth_id',0);
        if (config('app.env') != 'production') {
            $qrcodeUrlFormat = 'https://cdn.inwehub.com/demand/qrcode/2018/09/153733792816zoTjw.png?x-oss-process=image/resize,w_430,h_430,image/circle,r_300/format,png/watermark,image_cHJvZHVjdC9xcmNvZGUvMjAxOC8xMi8xNTQ1OTc1NDc3WTlMbzZLSi5wbmc=,g_center';
        } else {
            $qrcodeUrl = $this->getReviewQrcode($review->id,$oauth_id,$wxxcx);
            try {
                if ($review->hide) {
                    $qrcodeUrlFormat = $qrcodeUrl;
                } else {
                    $qrcodeUrlFormat = weapp_qrcode_replace_logo($qrcodeUrl,$review->user->avatar);
                }
            } catch (\Exception $e) {
                $qrcodeUrlFormat = $qrcodeUrl;
            }
        }
        $info = Tag::getReviewInfo($tag->id);
        $data = [
            'id' => $tag->id,
            'name' => $tag->name,
            'logo' => $tag->logo,
            'review_count' => $info['review_count'],
            'review_average_rate' => $info['review_average_rate']
        ];
        return view('h5::weapp.reviewShareLong')->with('review',$review)->with('qrcode',$qrcodeUrlFormat)->with('product',$data);
    }

    public function getReviewShareShortInfo($id,Request $request, WeApp $wxxcx){
        $review = Submission::find($id);
        $tag = Tag::find($review->category_id);
        $oauth_id = $request->input('oauth_id',0);
        if (config('app.env') != 'production') {
            $qrcodeUrlFormat = 'https://cdn.inwehub.com/demand/qrcode/2018/09/153733792816zoTjw.png?x-oss-process=image/resize,w_430,h_430,image/circle,r_300/format,png/watermark,image_cHJvZHVjdC9xcmNvZGUvMjAxOC8xMi8xNTQ1OTc1NDc3WTlMbzZLSi5wbmc=,g_center';
        } else {
            $qrcodeUrl = $this->getReviewQrcode($review->id,$oauth_id,$wxxcx);
            try {
                $qrcodeUrlFormat = weapp_qrcode_replace_logo($qrcodeUrl,$review->user->avatar);
            } catch (\Exception $e) {
                $qrcodeUrlFormat = $qrcodeUrl;
            }
        }
        $info = Tag::getReviewInfo($tag->id);
        $data = [
            'id' => $tag->id,
            'name' => $tag->name,
            'logo' => $tag->logo,
            'review_count' => $info['review_count'],
            'review_average_rate' => $info['review_average_rate']
        ];
        return view('h5::weapp.reviewShareShort')->with('review',$review)->with('qrcode',$qrcodeUrlFormat)->with('product',$data);
    }

    protected function getReviewQrcode($id,$oauth_id, WeApp $wxxcx) {
        $qrcodeUrl = RateLimiter::instance()->hGet('review-qrcode',$id.'_'.$oauth_id);
        if (!$qrcodeUrl) {
            $file_name = 'review/qrcode/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.png';
            $page = 'pages/commentDetail/commentDetail';
            $scene = '='.$id.'='.$oauth_id;
            try {
                $wxxcx->setConfig(config('weapp.appid_ask'),config('weapp.secret_ask'));
                $qrcode = $wxxcx->getQRCode()->getQRCodeB($scene,$page);
                Storage::disk('oss')->put($file_name,$qrcode);
                $qrcodeUrl = Storage::disk('oss')->url($file_name);
                RateLimiter::instance()->hSet('review-qrcode',$id,$qrcodeUrl);
            } catch (\Exception $e) {
                app('sentry')->captureException($e);
            }
        }
        return $qrcodeUrl;
    }

    protected function getAlbumQrcode($id,$oauth_id, WeApp $wxxcx) {
        $qrcodeUrl = RateLimiter::instance()->hGet('album-qrcode',$id.'_'.$oauth_id);
        if (!$qrcodeUrl) {
            $file_name = 'review/qrcode/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.png';
            $page = 'pages/specialDetail/specialDetail';
            $scene = '='.$id.'='.$oauth_id;
            try {
                $wxxcx->setConfig(config('weapp.appid_ask'),config('weapp.secret_ask'));
                $qrcode = $wxxcx->getQRCode()->getQRCodeB($scene,$page);
                Storage::disk('oss')->put($file_name,$qrcode);
                $qrcodeUrl = Storage::disk('oss')->url($file_name);
                RateLimiter::instance()->hSet('album-qrcode',$id,$qrcodeUrl);
            } catch (\Exception $e) {
                app('sentry')->captureException($e);
            }
        }
        return $qrcodeUrl;
    }

}