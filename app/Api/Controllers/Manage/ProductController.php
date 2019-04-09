<?php namespace App\Api\Controllers\Manage;
use App\Api\Controllers\Controller;

use App\Events\Frontend\System\ImportantNotify;
use App\Exceptions\ApiException;
use App\Jobs\UpdateProductInfoCache;
use App\Logic\TagsLogic;
use App\Models\Comment;
use App\Models\ContentCollection;
use App\Models\ProductUserRel;
use App\Models\Scraper\WechatMpInfo;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\Taggable;
use App\Models\UserOauth;
use App\Models\Weapp\Tongji;
use App\Services\RateLimiter;
use App\Services\Spiders\Wechat\MpSpider;
use App\Services\Spiders\Wechat\WechatSogouSpider;
use Illuminate\Http\Request;
use App\Third\Weapp\WeApp;
use Illuminate\Support\Facades\Storage;
use League\Glide\Api\Api;

/**
 * @author: wanghui
 * @date: 2017/4/20 下午8:40
 * @email: hank.huiwang@gmail.com
 */

class ProductController extends Controller {

    //获取产品信息
    public function getInfo(Request $request, WeApp $wxxcx) {
        $user = $request->user();
        $rel = ProductUserRel::where('user_id',$user->id)->where('status',1)->orderBy('id','desc')->first();
        if (!$rel) throw new ApiException(ApiException::USER_HAS_NOT_PRODUCT);
        $tag = $rel->tag;
        if (config('app.env') != 'production') {
            $qrcodeUrlFormat = 'https://cdn.inwehub.com/demand/qrcode/2018/09/153733792816zoTjw.png?x-oss-process=image/resize,w_430,h_430,image/circle,r_300/format,png/watermark,image_cHJvZHVjdC9xcmNvZGUvMjAxOC8xMi8xNTQ1OTc1NDc3WTlMbzZLSi5wbmc=,g_center';
        } else {
            $qrcodeUrl = $this->getProductQrcode($tag->id,0,$wxxcx);
            try {
                $qrcodeUrlFormat = weapp_qrcode_replace_logo($qrcodeUrl,$tag->logo);
            } catch (\Exception $e) {
                $qrcodeUrlFormat = $qrcodeUrl;
            }
        }
        $data = [
            'id' => $rel->tag_id,
            'name' => $tag->name,
            'log' => $tag->logo,
            'summary' => $tag->summary,
            'cover_pic' => $tag->getCoverPic(),
            'weappCodeUrl' => $qrcodeUrlFormat
        ];
        event(new ImportantNotify('[后台]'.formatSlackUser($user).'登陆后台'));
        return self::createJsonData(true,$data);
    }

    //更新产品信息
    public function updateInfo(Request $request) {
        $validateRules = [
            'id'   => 'required|integer',
            'name' => 'required',
            'summary' => 'required',
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = $request->input('id');
        $this->checkUserProduct($user->id,$id);
        $tag = Tag::find($id);

        $oldSummary = $tag->summary;
        $oldLogo = $tag->logo;
        $validateRules['name'] = 'required|max:128|unique:tags,name,'.$tag->id;
        $this->validate($request,$validateRules);
        $tag->name = $request->input('name');
        $tag->summary = $request->input('summary');

        $logo = $this->uploadImgs($request->input('logo'),'tags');
        if ($logo['img']) {
            $tag->logo = $logo['img'][0];
        }
        $cover_pic = $this->uploadImgs($request->input('cover_pic'),'tags');
        if ($cover_pic['img']) {
            $tag->setDescription(['cover_pic'=>$cover_pic['img'][0]]);
        }
        $tag->save();
        if($oldLogo != $tag->logo || $oldSummary != $tag->summary){
            $tag->clearMediaCollection('images_big');
            $tag->clearMediaCollection('images_small');
        }
        TagsLogic::cacheProductTags($tag);
        $this->dispatch(new UpdateProductInfoCache($tag->id));
        event(new ImportantNotify('[后台]'.formatSlackUser($user).'修改产品信息:'.$tag->name));
        return self::createJsonData(true);
    }

    //获取产品浏览统计信息
    public function getViewData(Request $request) {
        $validateRules = [
            'id'   => 'required|integer',
            'start_time' => 'required',
            'end_time' => 'required'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = $request->input('id');
        $this->checkUserProduct($user->id,$id);

        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $f = $request->input('f','array');
        $i = 0;
        $list = [];
        while ($start_time <= $end_time) {
            $count = Tongji::where('event_id',$id)->where('page','pages/majorProduct/majorProduct')
                ->where('created_at','>=',date('Y-m-d 00:00:00',$start_time))
                ->where('created_at','<=',date('Y-m-d 23:59:59',$start_time))->count();
            $list[] = [
                'ref_date' => date('Ymd',$start_time),
                'value'    => $count
            ];
            $start_time += 3600*24;
        }
        $return = ['list'=>$list];
        return self::createJsonData(true,$f=='array'?$return:json_encode($return));
    }

    //获取产品亮点图
    public function getIntroducePic(Request $request) {
        $validateRules = [
            'id'   => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = $request->input('id');
        $this->checkUserProduct($user->id,$id);
        $tag = Tag::find($id);
        $introduce_pic = $tag->getIntroducePic();
        if ($introduce_pic) {
            usort($introduce_pic,function ($a,$b) {
                if ($a['sort'] == $b['sort']) {
                    return 0;
                }
                return ($a['sort'] < $b['sort']) ? -1 : 1;
            });
        }
        return self::createJsonData(true,[
            'id' => $id,
            'introduce_pic' => $introduce_pic?array_column($introduce_pic,'url'):[]
        ]);
    }

    //更新产品亮点图
    public function updateIntroducePic(Request $request) {
        $validateRules = [
            'id'   => 'required|integer',
            'introduce_pic_arr' => 'required|array'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = $request->input('id');
        $this->checkUserProduct($user->id,$id);
        $tag = Tag::find($id);
        $images = $tag->getIntroducePic();
        if ($images) {
            usort($images,function ($a,$b) {
                if ($a['sort'] == $b['sort']) {
                    return 0;
                }
                return ($a['sort'] < $b['sort']) ? -1 : 1;
            });
            $baseSort = $images[count($images)-1]['sort']+1;
        } else {
            $baseSort = 0;
        }
        $imgUrls = [];
        $imgs = $this->uploadImgs($request->input('introduce_pic_arr'),'tags');
        foreach ($imgs['img'] as $key=>$img) {
            $imgUrls[] = ['url'=>$img,'sort'=>$baseSort+$key];
        }
        $tag->setDescription(['introduce_pic'=>array_merge($images,$imgUrls)]);
        $tag->save();
        $this->dispatch(new UpdateProductInfoCache($tag->id));
        $introduce_pic = $tag->getIntroducePic();
        event(new ImportantNotify('[后台]'.formatSlackUser($user).'更新产品亮点图:'.$tag->name));
        return self::createJsonData(true,[
            'id' => $id,
            'introduce_pic' => $introduce_pic?array_column($introduce_pic,'url'):[]
        ]);
    }

    //删除产品亮点图
    public function deleteIntroducePic(Request $request) {
        $validateRules = [
            'id'   => 'required|integer',
            'introduce_pic' => 'required'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = $request->input('id');
        $url = $request->input('introduce_pic');
        $this->checkUserProduct($user->id,$id);
        $tag = Tag::find($id);
        $images = $tag->getIntroducePic();
        foreach ($images as $i=>$image) {
            if ($image['url'] == $url) {
                unset($images[$i]);
            }
        }
        $tag->setDescription(['introduce_pic'=>$images]);
        $tag->save();
        $this->dispatch(new UpdateProductInfoCache($tag->id));
        event(new ImportantNotify('[后台]'.formatSlackUser($user).'删除产品亮点图:'.$tag->name));
        return self::createJsonData(true);
    }

    //排序产品亮点图
    public function sortIntroducePic(Request $request) {
        $validateRules = [
            'id'   => 'required|integer',
            'introduce_pic_arr' => 'required|array'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = $request->input('id');
        $this->checkUserProduct($user->id,$id);
        $tag = Tag::find($id);
        $newList = $request->input('introduce_pic_arr');
        $urls = [];
        foreach ($newList as $key=>$item) {
            $urls[] = [
                'sort' => $key,
                'url' => $item['extra']['url']
            ];
        }
        $tag->setDescription(['introduce_pic'=>$urls]);
        $tag->save();
        $this->dispatch(new UpdateProductInfoCache($tag->id));
        event(new ImportantNotify('[后台]'.formatSlackUser($user).'排序产品信息:'.$tag->name));
        return self::createJsonData(true,[
            'id' => $id,
            'introduce_pic' => $newList
        ]);
    }

    //产品专家观点列表
    public function ideaList(Request $request) {
        $validateRules = [
            'product_id'   => 'required|integer',
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = $request->input('product_id');
        $this->checkUserProduct($user->id,$id);
        $perPage = $request->input('perPage',200);
        $ideas = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_TAG_EXPERT_IDEA)
            ->where('source_id',$id)
            ->whereIn('status',[0,1])
            ->orderBy('sort','desc')->paginate($perPage);
        $list = [];
        $return = $ideas->toArray();
        foreach ($ideas as $idea) {
            $list[] = [
                'id' => $idea->id,
                'avatar' => $idea->content['avatar'],
                'name' => $idea->content['name'],
                'title' => $idea->content['title'],
                'content' => $idea->content['content'],
                'sort' => $idea->sort,
                'status' => $idea->status
            ];
        }
        $return['data'] = $list;
        return self::createJsonData(true,$return);
    }

    //显示|隐藏|删除观点
    public function updateIdeaStatus(Request $request) {
        $validateRules = [
            'idea_id'   => 'required|integer',
            'status'    => 'required|in:0,1,3'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = $request->input('idea_id');
        $idea = ContentCollection::find($id);
        $this->checkUserProduct($user->id,$idea->source_id);
        $idea->status = $request->input('status');
        $idea->save();
        $this->dispatch(new UpdateProductInfoCache($idea->source_id));
        $tag = Tag::find($idea->source_id);
        event(new ImportantNotify('[后台]'.formatSlackUser($user).'修改产品观点状态:'.$tag->name));
        return self::createJsonData(true,['status'=>$request->input('status')]);
    }

    //排序专家观点
    public function sortIdea(Request $request) {
        $validateRules = [
            'idea_id'   => 'required|integer',
            'to_idea_id'    => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = $request->input('idea_id');
        $to_idea_id = $request->input('to_idea_id');
        $idea = ContentCollection::find($id);
        $toIdea = ContentCollection::find($to_idea_id);
        $this->checkUserProduct($user->id,$idea->source_id);
        $oldSort = $idea->sort;
        $idea->sort = $toIdea->sort;
        $toIdea->sort = $oldSort;
        $idea->save();
        $toIdea->save();
        $this->dispatch(new UpdateProductInfoCache($idea->source_id));
        $tag = Tag::find($idea->source_id);
        event(new ImportantNotify('[后台]'.formatSlackUser($user).'排序产品观点状态:'.$tag->name));
        return self::createJsonData(true);
    }

    //更新专家观点
    public function updateIdea(Request $request) {
        $validateRules = [
            'idea_id'   => 'required|integer',
            'name'    => 'required',
            'title'   => 'required',
            'content' => 'required',
            'avatar' => 'required'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = $request->input('idea_id');
        $idea = ContentCollection::find($id);
        $this->checkUserProduct($user->id,$idea->source_id);
        $img = $this->uploadImgs($request->input('avatar'),'tags');
        $data = $request->all();
        $idea->update([
            'content' => [
                'avatar' => $img['img'][0],
                'name' => $data['name'],
                'title' => $data['title'],
                'content' => $data['content']
            ]
        ]);
        $this->dispatch(new UpdateProductInfoCache($idea->source_id));
        $tag = Tag::find($idea->source_id);
        event(new ImportantNotify('[后台]'.formatSlackUser($user).'更新产品观点:'.$tag->name));
        return self::createJsonData(true,['id'=>$id]);
    }

    //添加观点
    public function storeIdea(Request $request) {
        $validateRules = [
            'id'   => 'required|integer',
            'name'    => 'required',
            'title'   => 'required',
            'content' => 'required',
            'avatar' => 'required'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = $request->input('id');
        $this->checkUserProduct($user->id,$id);
        $img = $this->uploadImgs($request->input('avatar'));
        $data = $request->all();
        $lastIdea = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_TAG_EXPERT_IDEA)
            ->where('source_id',$id)->orderBy('sort','desc')->first();
        $model = ContentCollection::create([
            'content_type' => ContentCollection::CONTENT_TYPE_TAG_EXPERT_IDEA,
            'sort' => $lastIdea?$lastIdea->sort+1:0,
            'source_id' => $id,
            'content' => [
                'avatar' => $img['img'][0],
                'name' => $data['name'],
                'title' => $data['title'],
                'content' => $data['content']
            ]
        ]);
        $this->dispatch(new UpdateProductInfoCache($id));
        $tag = Tag::find($id);
        event(new ImportantNotify('[后台]'.formatSlackUser($user).'添加产品观点:'.$tag->name));
        return self::createJsonData(true,['id'=>$model->id]);
    }

    //产品案例列表
    public function caseList(Request $request) {
        $validateRules = [
            'product_id'   => 'required|integer',
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = $request->input('product_id');
        $this->checkUserProduct($user->id,$id);
        $perPage = $request->input('perPage',200);
        $caseList = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_TAG_SHOW_CASE)
            ->where('source_id',$id)
            ->whereIn('status',[0,1])
            ->orderBy('sort','desc')->paginate($perPage);
        $list = [];
        $return = $caseList->toArray();
        foreach ($caseList as $case) {
            $list[] = [
                'id' => $case->id,
                'title' => $case->content['title'],
                'desc' => $case->content['desc'],
                'cover_pic' => $case->content['cover_pic'],
                'type' => $case->content['type'],
                'link_url' => $case->content['link_url'],
                'status' => $case->status,
                'sort' => $case->sort
            ];
        }
        $return['data'] = $list;
        return self::createJsonData(true,$return);
    }

    //排序产品案例
    public function sortCase(Request $request) {
        $validateRules = [
            'case_id'   => 'required|integer',
            'to_case_id'    => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = $request->input('case_id');
        $to_idea_id = $request->input('to_case_id');
        $idea = ContentCollection::find($id);
        $toIdea = ContentCollection::find($to_idea_id);
        $this->checkUserProduct($user->id,$idea->source_id);
        $oldSort = $idea->sort;
        $idea->sort = $toIdea->sort;
        $toIdea->sort = $oldSort;
        $idea->save();
        $toIdea->save();
        $this->dispatch(new UpdateProductInfoCache($idea->source_id));
        $tag = Tag::find($id);
        event(new ImportantNotify('[后台]'.formatSlackUser($user).'排序产品案例:'.$tag->name));
        return self::createJsonData(true);
    }

    //显示|隐藏|删除案例
    public function updateCaseStatus(Request $request) {
        $validateRules = [
            'case_id'   => 'required|integer',
            'status'    => 'required|in:0,1,3'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = $request->input('case_id');
        $idea = ContentCollection::find($id);
        $this->checkUserProduct($user->id,$idea->source_id);
        $idea->status = $request->input('status');
        $idea->save();
        $this->dispatch(new UpdateProductInfoCache($idea->source_id));
        $tag = Tag::find($idea->source_id);
        event(new ImportantNotify('[后台]'.formatSlackUser($user).'修改产品案例状态:'.$tag->name));
        return self::createJsonData(true,['status'=>$request->input('status')]);
    }

    //更新案例
    public function updateCase(Request $request) {
        $validateRules = [
            'case_id'   => 'required|integer',
            'desc'    => 'required',
            'title'   => 'required',
            'cover_pic' => 'required',
            'type' => 'required|in:link,pdf,image',
            'link_url' => 'required_if:type,link,video',
            'file' => 'required_if:type,pdf,image'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = $request->input('case_id');
        $case = ContentCollection::find($id);
        $this->checkUserProduct($user->id,$case->source_id);
        $data = $request->all();
        $content = $case->content;

        if ($data['type'] == 'link' && $data['link_url'] != $case->content['link_url']) {
            $link_url = parse_url($data['link_url']);
            if ($link_url['host'] != 'mp.weixin.qq.com') {
                throw new ApiException(ApiException::PRODUCT_CASE_URL_INVALID);
            }
            $linkInfo = getWechatUrlInfo($data['link_url'],false,true);
            $mpInfo = WechatMpInfo::where('wx_hao',$linkInfo['wxHao'])->first();
            if (!$mpInfo) {
                $mpInfo = WechatMpInfo::create([
                    'name' => $linkInfo['author'],
                    'wx_hao' => $linkInfo['wxHao'],
                    'company' => $linkInfo['author'],
                    'description' => '',
                    'logo_url' => '',
                    'qr_url' => '',
                    'wz_url' => '',
                    'last_qunfa_id' => 0,
                    'status' => 0,
                    'create_time' => date('Y-m-d H:i:s')
                ]);
            }
            $article_uuid = base64_encode($mpInfo->_id.$linkInfo['title'].date('Y-m-d',$linkInfo['date']));
            $aid = RateLimiter::instance()->hGet('wechat_article',$article_uuid);
            if ($aid) {
                $article = WechatWenzhangInfo::find($aid);
                $article->type = WechatWenzhangInfo::TYPE_TAG_NEWS;
                $article->save();
            } else {
                $article = WechatWenzhangInfo::create([
                    'title' => $linkInfo['title'],
                    'source_url' => '',
                    'content_url' => $data['link_url'],
                    'cover_url'   => saveImgToCdn($linkInfo['cover_img'],'submissions'),
                    'description' => '',
                    'date_time'   => date('Y-m-d H:i:s',$linkInfo['date']),
                    'mp_id' => $mpInfo->_id,
                    'author' => $linkInfo['author'],
                    'msg_index' => 0,
                    'copyright_stat' => 0,
                    'qunfa_id' => 0,
                    'body' => $linkInfo['body'],
                    'type' => WechatWenzhangInfo::TYPE_TAG_NEWS,
                    'like_count' => 0,
                    'read_count' => 0,
                    'status' => 2,
                    'comment_count' => 0
                ]);
                RateLimiter::instance()->hSet('wechat_article',$article_uuid,$article->_id);
            }
            $content['link_url'] = config('app.url').'/articleInfo/'.$article->_id.'?inwehub_user_device=weapp_dianping&source=product_'.$tag->id;
        }

        if ($data['type'] == 'image') {
            $images = $this->uploadImgs($data['file'],'tags');
            $content['link_url'] = $images['img'][0];
        }

        if ($data['type'] == 'pdf') {
            $files = $this->uploadFile([$data['file']],'tags');
            if ($files) {
                $content['link_url'] = $files[0]['url'];
            }
        }

        $covers = $this->uploadImgs($data['cover_pic'],'tags');
        $content['cover_pic'] = $covers['img'][0];
        $content['title'] = $data['title'];
        $content['type'] = $data['type'];
        $content['desc'] = $data['desc'];
        $case->content = $content;
        $case->save();

        $this->dispatch(new UpdateProductInfoCache($case->source_id));
        $tag = Tag::find($case->source_id);
        event(new ImportantNotify('[后台]'.formatSlackUser($user).'更新产品案例:'.$tag->name));
        return self::createJsonData(true,['id'=>$id]);
    }

    //添加案例
    public function storeCase(Request $request) {
        $validateRules = [
            'id' => 'required',
            'title' => 'required|max:128',
            'desc' => 'required',
            'cover_pic' => 'required',
            'type' => 'required',
            'link_url' => 'required_if:type,link,video',
            'file' => 'required_if:type,pdf,image'
        ];
        $this->validate($request,$validateRules);
        $data = $request->all();
        $id = $request->input('id');
        $tag = Tag::find($id);
        $user = $request->user();
        $this->checkUserProduct($user->id,$id);

        if ($data['type'] == 'link') {
            $link_url = parse_url($data['link_url']);
            if ($link_url['host'] != 'mp.weixin.qq.com') {
                throw new ApiException(ApiException::PRODUCT_CASE_URL_INVALID);
            }
            $linkInfo = getWechatUrlInfo($data['link_url'],false,true);
            $mpInfo = WechatMpInfo::where('wx_hao',$linkInfo['wxHao'])->first();
            if (!$mpInfo) {
                $mpInfo = WechatMpInfo::create([
                    'name' => $linkInfo['author'],
                    'wx_hao' => $linkInfo['wxHao'],
                    'company' => $linkInfo['author'],
                    'description' => '',
                    'logo_url' => '',
                    'qr_url' => '',
                    'wz_url' => '',
                    'last_qunfa_id' => 0,
                    'status' => 0,
                    'create_time' => date('Y-m-d H:i:s')
                ]);
            }
            $article_uuid = base64_encode($mpInfo->_id.$linkInfo['title'].date('Y-m-d',$linkInfo['date']));
            $aid = RateLimiter::instance()->hGet('wechat_article',$article_uuid);
            if ($aid) {
                $article = WechatWenzhangInfo::find($aid);
                $article->type = WechatWenzhangInfo::TYPE_TAG_NEWS;
                $article->save();
            } else {
                $article = WechatWenzhangInfo::create([
                    'title' => $linkInfo['title'],
                    'source_url' => '',
                    'content_url' => $data['link_url'],
                    'cover_url'   => saveImgToCdn($linkInfo['cover_img'],'submissions'),
                    'description' => '',
                    'date_time'   => date('Y-m-d H:i:s',$linkInfo['date']),
                    'mp_id' => $mpInfo->_id,
                    'author' => $linkInfo['author'],
                    'msg_index' => 0,
                    'copyright_stat' => 0,
                    'qunfa_id' => 0,
                    'body' => $linkInfo['body'],
                    'type' => WechatWenzhangInfo::TYPE_TAG_NEWS,
                    'like_count' => 0,
                    'read_count' => 0,
                    'status' => 2,
                    'comment_count' => 0
                ]);
                RateLimiter::instance()->hSet('wechat_article',$article_uuid,$article->_id);
            }
            $data['link_url'] = config('app.url').'/articleInfo/'.$article->_id.'?inwehub_user_device=weapp_dianping&source=product_'.$tag->id;
        }
        if ($data['type'] == 'image') {
            $images = $this->uploadImgs($data['file'],'tags');
            $data['link_url'] = $images['img'][0];
        }

        if ($data['type'] == 'pdf') {
            $files = $this->uploadFile([$data['file']],'tags');
            $data['link_url'] = $files[0]['url'];
        }
        $covers = $this->uploadImgs($data['cover_pic'],'tags');
        $cover_pic = $covers['img'][0];
        $lastCase = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_TAG_SHOW_CASE)
            ->where('source_id',$id)->orderBy('sort','desc')->first();
        $model = ContentCollection::create([
            'content_type' => ContentCollection::CONTENT_TYPE_TAG_SHOW_CASE,
            'sort' => $lastCase?$lastCase->sort+1:0,
            'source_id' => $id,
            'status' => 1,
            'content' => [
                'cover_pic' => $cover_pic,
                'title' => $data['title'],
                'desc' => $data['desc'],
                'link_url' => $data['link_url'],
                'type' => $data['type']
            ]
        ]);
        $this->dispatch(new UpdateProductInfoCache($id));
        event(new ImportantNotify('[后台]'.formatSlackUser($user).'添加产品案例:'.$tag->name));
        return self::createJsonData(true,['id'=>$model->id]);
    }

    // 产品资讯列表
    public function newsList(Request $request) {
        $validateRules = [
            'product_id' => 'required'
        ];
        $this->validate($request,$validateRules);
        $id = $request->input('product_id');
        $tag = Tag::find($id);
        $user = $request->user();
        $this->checkUserProduct($user->id,$id);
        $query = WechatWenzhangInfo::where('source_type',1)
            ->where('type',WechatWenzhangInfo::TYPE_TAG_NEWS)
            ->whereHas('tags',function($query) use ($id) {
                $query->where('tag_id', $id);
            });
        $newsList = $query->orderBy('_id','desc')->paginate($request->input('perPage',20));
        $data = $newsList->toArray();
        $list = [];
        foreach ($newsList as $news) {
            $taggable = Taggable::where('tag_id',$id)
                ->where('taggable_id',$news->_id)
                ->where('taggable_type',get_class($news))
                ->first();
            $list[] = [
                'id' => $news->_id,
                'title' => $news->title,
                'author' => $news->author,
                'link_url' => config('app.url').'/articleInfo/'.$news->_id.'?inwehub_user_device=weapp_admin',
                'status' => $taggable->is_display,
                'date' => $news->date_time
            ];
        }
        $data['data'] = $list;
        return self::createJsonData(true,$data);
    }

    //显示|隐藏|删除资讯
    public function updateNewsStatus(Request $request) {
        $validateRules = [
            'product_id' => 'required',
            'news_id'   => 'required|integer',
            'status'    => 'required|in:0,1,3'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = $request->input('news_id');
        $product_id = $request->input('product_id');
        $status = $request->input('status');
        $this->checkUserProduct($user->id,$product_id);

        switch ($status) {
            case 0:
            case 1:
                $taggable = Taggable::where('tag_id',$product_id)
                    ->where('taggable_id',$id)
                    ->where('taggable_type',WechatWenzhangInfo::class)
                    ->first();
                $taggable->is_display = $status;
                $taggable->save();
                break;
            case 3:
                Taggable::where('tag_id',$product_id)
                    ->where('taggable_id',$id)
                    ->where('taggable_type',WechatWenzhangInfo::class)->delete();
                break;
        }

        $this->dispatch(new UpdateProductInfoCache($product_id));
        $tag = Tag::find($product_id);
        event(new ImportantNotify('[后台]'.formatSlackUser($user).'更新产品资讯状态:'.$tag->name));
        return self::createJsonData(true,['status'=>$request->input('status')]);
    }

    //添加资讯
    public function storeNews(Request $request) {
        $validateRules = [
            'product_id' => 'required',
            'url'   => 'required|url',
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $product_id = $request->input('product_id');
        $this->checkUserProduct($user->id,$product_id);
        $link_url = $request->input('url');
        $parse_url = parse_url($link_url);
        if ($parse_url['host'] != 'mp.weixin.qq.com') {
            throw new ApiException(ApiException::PRODUCT_CASE_URL_INVALID);
        }
        $linkInfo = getWechatUrlInfo($link_url,false,true);
        $mpInfo = WechatMpInfo::where('wx_hao',$linkInfo['wxHao'])->first();
        if (!$mpInfo) {
            $mpInfo = WechatMpInfo::create([
                'name' => $linkInfo['author'],
                'wx_hao' => $linkInfo['wxHao'],
                'company' => $linkInfo['author'],
                'description' => '',
                'logo_url' => '',
                'qr_url' => '',
                'wz_url' => '',
                'last_qunfa_id' => 0,
                'status' => 0,
                'create_time' => date('Y-m-d H:i:s')
            ]);
        }
        $article_uuid = base64_encode($mpInfo->_id.$linkInfo['title'].date('Y-m-d',$linkInfo['date']));
        $aid = RateLimiter::instance()->hGet('wechat_article',$article_uuid);
        if ($aid) {
            $article = WechatWenzhangInfo::find($aid);
            $article->type = WechatWenzhangInfo::TYPE_TAG_NEWS;
            $article->save();
        } else {
            $article = WechatWenzhangInfo::create([
                'title' => $linkInfo['title'],
                'source_url' => '',
                'content_url' => $link_url,
                'cover_url'   => saveImgToCdn($linkInfo['cover_img'],'submissions'),
                'description' => '',
                'date_time'   => date('Y-m-d H:i:s',$linkInfo['date']),
                'mp_id' => $mpInfo->_id,
                'author' => $linkInfo['author'],
                'msg_index' => 0,
                'body' => $linkInfo['body'],
                'copyright_stat' => 0,
                'qunfa_id' => 0,
                'type' => WechatWenzhangInfo::TYPE_TAG_NEWS,
                'like_count' => 0,
                'status' => 2,
                'read_count' => 0,
                'comment_count' => 0
            ]);
            RateLimiter::instance()->hSet('wechat_article',$article_uuid,$article->_id);
        }
        Tag::multiAddByIds([$product_id],$article);
        $this->dispatch(new UpdateProductInfoCache($product_id));
        $tag = Tag::find($product_id);
        event(new ImportantNotify('[后台]'.formatSlackUser($user).'添加产品资讯:'.$tag->name));
        return self::createJsonData(true,['id'=>$article->_id]);
    }

    //获取url标题
    public function fetchUrlInfo(Request $request) {
        $validateRules = [
            'url'   => 'required|url',
        ];
        $this->validate($request,$validateRules);
        $link_url = $request->input('url');
        $parse_url = parse_url($link_url);
        if ($parse_url['host'] != 'mp.weixin.qq.com') {
            throw new ApiException(ApiException::PRODUCT_CASE_URL_INVALID);
        }
        $linkInfo = getWechatUrlInfo($link_url,false,true);
        $mpInfo = WechatMpInfo::where('wx_hao',$linkInfo['wxHao'])->first();
        if (!$mpInfo) {
            $mpInfo = WechatMpInfo::create([
                'name' => $linkInfo['author'],
                'wx_hao' => $linkInfo['wxHao'],
                'company' => $linkInfo['author'],
                'description' => '',
                'logo_url' => '',
                'qr_url' => '',
                'wz_url' => '',
                'last_qunfa_id' => 0,
                'status' => 0,
                'create_time' => date('Y-m-d H:i:s')
            ]);
        }
        $article_uuid = base64_encode($mpInfo->_id.$linkInfo['title'].date('Y-m-d',$linkInfo['date']));
        $aid = RateLimiter::instance()->hGet('wechat_article',$article_uuid);
        if ($aid) {
            $article = WechatWenzhangInfo::find($aid);
        } else {
            $article = WechatWenzhangInfo::create([
                'title' => $linkInfo['title'],
                'source_url' => '',
                'content_url' => $link_url,
                'cover_url'   => saveImgToCdn($linkInfo['cover_img'],'submissions'),
                'description' => '',
                'date_time'   => date('Y-m-d H:i:s',$linkInfo['date']),
                'mp_id' => $mpInfo->_id,
                'author' => $linkInfo['author'],
                'msg_index' => 0,
                'body' => $linkInfo['body'],
                'copyright_stat' => 0,
                'qunfa_id' => 0,
                'type' => WechatWenzhangInfo::TYPE_TAG_NEWS,
                'like_count' => 0,
                'status' => 2,
                'read_count' => 0,
                'comment_count' => 0
            ]);
            RateLimiter::instance()->hSet('wechat_article',$article_uuid,$article->_id);
        }
        return self::createJsonData(true,[
            'title' => $article->title,
            'author' => $article->author,
            'date' => $article->date_time
        ]);
    }

    //产品内容源列表
    public function sourceList(Request $request) {
        $validateRules = [
            'product_id' => 'required'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $product_id = $request->input('product_id');
        $this->checkUserProduct($user->id,$product_id);
        $gzhList = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_TAG_WECHAT_GZH)
            ->where('source_id',$product_id)->orderBy('id','desc')->paginate($request->input('perPage',20));
        $data = $gzhList->toArray();
        $list = [];
        foreach ($gzhList as $gzh) {
            $source = WechatMpInfo::where('wx_hao',$gzh->content['wx_hao'])->first();
            $list[] = [
                'id' => $gzh->id,
                'name' => $source->name,
                'type' => '公众号',
                'news_count' => $source->countTotalArticle(),
                'last_update' => $source->update_time
            ];
        }
        $data['data'] = $list;
        return self::createJsonData(true,$data);
    }

    //删除内容源
    public function delSource(Request $request) {
        $validateRules = [
            'source_id'   => 'required|integer',
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = $request->input('source_id');
        $model = ContentCollection::find($id);
        $this->checkUserProduct($user->id,$model->source_id);
        $model->delete();
        $this->dispatch(new UpdateProductInfoCache($model->source_id));
        $tag = Tag::find($model->source_id);
        event(new ImportantNotify('[后台]'.formatSlackUser($user).'删除产品内容源:'.$tag->name));
        return self::createJsonData(true);
    }

    //添加内容源
    public function storeSource(Request $request) {
        $validateRules = [
            'id' => 'required',
            'source_id'   => 'required',
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $source_id = trim($request->input('source_id'));
        $product_id = $request->input('id');
        if (count(parse_url($source_id))>=2) {
            throw new ApiException(ApiException::PRODUCT_SOURCE_URL_INVALID);
        }
        $mpInfo = WechatMpInfo::find($source_id);
        if (!$mpInfo) {
            throw new ApiException(ApiException::REQUEST_FAIL);
        }
        $exist = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_TAG_WECHAT_GZH)
            ->where('source_id',$product_id)
            ->where('sort',$mpInfo->_id)
            ->first();
        if (!$exist) {
            $exist = ContentCollection::create([
                'content_type' => ContentCollection::CONTENT_TYPE_TAG_WECHAT_GZH,
                'sort' => $mpInfo->_id,
                'source_id' => $product_id,
                'status' => 1,
                'content' => [
                    'wx_hao' => $mpInfo->wx_hao,
                    'mp_id' => $mpInfo->_id
                ]
            ]);
        }
        if ($mpInfo->status != 1) {
            $mpInfo->status = 1;
            $mpInfo->save();
        }
        event(new ImportantNotify('[后台]'.formatSlackUser($user).'添加产品内容源:'.$wx_hao));
        return self::createJsonData(true,['id'=>$exist->id]);
    }

    //获取源信息
    public function fetchSourceInfo(Request $request) {
        $validateRules = [
            'product_id' => 'required',
            'source'   => 'required',
        ];
        $this->validate($request,$validateRules);
        $wx_hao = trim($request->input('source'));
        $product_id = $request->input('product_id');
        if (count(parse_url($wx_hao))>=2) {
            throw new ApiException(ApiException::PRODUCT_SOURCE_URL_INVALID);
        }
        $mpInfo = WechatMpInfo::where('wx_hao',$wx_hao)->first();
        if ($mpInfo) {
            return self::createJsonData(true,['title'=>$mpInfo->name,'source_id'=>$mpInfo->_id]);
        }
        $spider = new MpSpider();
        if (config('app.env') == 'production') {
            $data = $spider->getGzhInfo($wx_hao,false);
        } else {
            $data = null;
        }

        if ($data) {
            $info = WechatMpInfo::where('wx_hao',$wx_hao)->first();
            if (!$info) {
                $mpInfo = WechatMpInfo::create([
                    'name' => $data['name'],
                    'wx_hao' => $data['wechatid'],
                    'company' => $data['company'],
                    'description' => $data['description'],
                    'logo_url' => $data['img'],
                    'qr_url' => $data['qrcode'],
                    'wz_url' => $data['url'],
                    'last_qunfa_id' => $data['last_qunfa_id'],
                    'is_auto_publish' => 0,
                    'status' => 0,
                    'create_time' => date('Y-m-d H:i:s')
                ]);
            }
        } else {
            $spider2 = new WechatSogouSpider();
            $data = $spider2->getGzhInfo($wx_hao,true);
            if ($data['name']) {
                $info = WechatMpInfo::where('wx_hao',$wx_hao)->first();
                if (!$info) {
                    $mpInfo = WechatMpInfo::create([
                        'name' => $data['name'],
                        'wx_hao' => $data['wechatid'],
                        'company' => $data['company'],
                        'description' => $data['description'],
                        'logo_url' => $data['img'],
                        'qr_url' => $data['qrcode'],
                        'wz_url' => $data['url'],
                        'is_auto_publish' => 0,
                        'status' => 0,
                        'last_qunfa_id' => $data['last_qunfa_id'],
                        'create_time' => date('Y-m-d H:i:s')
                    ]);
                }
            } else {
                throw new ApiException(ApiException::REQUEST_FAIL);
            }
        }
        if ($mpInfo) {
            return self::createJsonData(true,['title'=>$mpInfo->name,'source_id'=>$mpInfo->_id]);
        } else {
            throw new ApiException(ApiException::REQUEST_FAIL);
        }
    }

    //删除点评
    public function delDianping(Request $request) {
        $validateRules = [
            'id' => 'required'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = trim($request->input('id'));
        $review = Submission::find($id);
        if ($review->type != 'review') {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $product_id = $review->category_id;
        $this->checkUserProduct($user->id,$product_id);
        $review->status = 0;
        $review->save();
        return self::createJsonData(true);
    }

    //加精点评
    public function recommendDianping(Request $request) {
        $validateRules = [
            'id' => 'required'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = trim($request->input('id'));
        $review = Submission::find($id);
        if ($review->type != 'review') {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $product_id = $review->category_id;
        $this->checkUserProduct($user->id,$product_id);
        $review->is_recommend = 1;
        $review->save();
        return self::createJsonData(true);
    }

    //官方回复点评
    public function officialReplyDianping(Request $request) {
        $validateRules = [
            'id' => 'required',
            'content' => 'required'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = trim($request->input('id'));
        $review = Submission::find($id);
        if ($review->type != 'review') {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $product_id = $review->category_id;
        $this->checkUserProduct($user->id,$product_id);
        $comment = Comment::where('source_id',$id)->where('source_type',get_class($review))
            ->where('comment_type',Comment::COMMENT_TYPE_OFFICIAL)->first();

        $data = [
            'content'          => trim($request->input('content')),
            'user_id'       => $user->id,
            'parent_id'     => 0,
            'level'         => 0,
            'source_id' => $id,
            'source_type' => get_class($review),
            'to_user_id'  => 0,
            'supports'    => 0,
            'comment_type' => Comment::COMMENT_TYPE_OFFICIAL,
            'mentions' => [],
            'status' => 1
        ];
        if (!$comment) {
            $comment = Comment::create($data);
        } else {
            $comment->content = $data['content'];
            $comment->status = 1;
            $comment->save();
        }
        return self::createJsonData(true);
    }

    //删除官方回复
    public function delOfficialReplyDianping(Request $request) {
        $validateRules = [
            'id' => 'required',
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = trim($request->input('id'));
        $comment = Comment::find($id);
        if ($comment->comment_type != Comment::COMMENT_TYPE_OFFICIAL && $comment->source_type != Submission::class) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $product_id = $comment->source_id;
        $this->checkUserProduct($user->id,$product_id);
        $comment->delete();
        return self::createJsonData(true);
    }

    //点评列表
    public function dianpingList(Request $request) {
        $validateRules = [
            'product_id' => 'required',
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = trim($request->input('product_id'));
        $this->checkUserProduct($user->id,$id);
        $perPage = $request->input('perPage',20);
        $word = $request->input('search_word');
        $query = Submission::where('status',1)->where('category_id',$id)->where('type','review');
        $submissions = $query->orderBy('id','desc')->paginate($perPage);
        $return = $submissions->toArray();
        $list = [];
        foreach ($submissions as $submission) {
            $comment = Comment::where('source_id',$submission->id)->where('source_type',get_class($submission))
                ->where('comment_type',Comment::COMMENT_TYPE_OFFICIAL)->where('status',1)->first();
            $official_reply = '';
            if ($comment) {
                $official_reply = [
                    'id' => $comment->id,
                    'author' => '官方回复',
                    'content'=>$comment->content,
                    'created_at' => (string)$comment->created_at
                ];
            }
            $oauth_id = '';
            $nickname = '匿名';
            $avatar = config('image.user_default_avatar');

            if (!$submission->hide) {
                $oauth = UserOauth::where('user_id',$submission->user_id)->where('status',1)->orderBy('id','desc')->first();
                if ($oauth) {
                    $oauth_id = $oauth->id;
                    $nickname = $oauth->nickname;
                    $avatar = $oauth->avatar;
                } else {
                    $oauth_id = '';
                    $nickname = $submission->user->name;
                    $avatar = $submission->user->avatar;
                }
            }
            $list[] = [
                'id' => $submission->id,
                'content' => $submission->title,
                'rate_star' => $submission->rate_star,
                'official_reply' => $official_reply,
                'is_recommend' => $submission->is_recommend,
                'user'  => [
                    'oauth_id'  => $oauth_id,
                    'nickname'  => $nickname,
                    'avatar'=> $avatar
                ],
                'created_at' => (string)$submission->created_at
            ];
        }
        $return['data'] = $list;
        return self::createJsonData(true, $return);
    }


    //分析-用户列表
    public function visitedUserList(Request $request) {
        $validateRules = [
            'product_id' => 'required',
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $id = trim($request->input('product_id'));
        $this->checkUserProduct($user->id,$id);
        $perPage = $request->input('perPage',20);
        $query = Tongji::where('event_id',$id);
    }



    protected function checkUserProduct($uid,$pid) {
        $rel = ProductUserRel::where('user_id',$uid)->where('tag_id',$pid)->where('status',1)->orderBy('id','desc')->first();
        if (!$rel) throw new ApiException(ApiException::USER_HAS_NOT_PRODUCT);
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


}