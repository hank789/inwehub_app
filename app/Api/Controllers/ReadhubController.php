<?php

namespace App\Api\Controllers;

use App\Exceptions\ApiException;
use App\Logic\QuillLogic;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ReadhubController extends Controller
{

    public function mySubmission(Request $request){
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);
        $uuid = $request->input('uuid');
        $type = $request->input('type',1);
        $loginUser = $request->user();
        if ($uuid) {
            $user = User::where('uuid',$uuid)->first();
            if (!$user) {
                throw new ApiException(ApiException::BAD_REQUEST);
            }
        } else {
            $user = $request->user();
        }

        $query = Submission::where('user_id',$user->id);
        if ($user->id != $loginUser->id) {
            $query = $query->where('public',1)->where('hide',0)->where('status',1);
        }
        switch ($type) {
            case 3:
                $query = $query->where('type','article');
                break;
            case 2:
                $query = $query->where('type','review');
                break;
            default:
                $query = $query->where('type','!=','review');
                break;
        }

        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }
        $submissions = $query->orderBy('id','DESC')->paginate(Config::get('inwehub.api_data_page_size'));

        $list = [];
        foreach($submissions as $submission){
            if ($submission->type == 'review') {
                $comment_url = '/dianping/comment/'.$submission->slug;
                $tags = $submission->tags()->wherePivot('is_display',1)->get()->toArray();
                $category_name = $tags[0]['name'];
            } else {
                $comment_url = '/c/'.$submission->category_id.'/'.$submission->slug;
                $group = Group::find($submission->group_id);
                $category_name = $group->name;
            }

            $list[] = [
                'id' => $submission->id,
                'type' => $submission->type,
                'title' => $submission->formatTitle(),
                'description' => isset($submission->data['description'])?str_limit(QuillLogic::parseText($submission->data['description']),200):'',
                'slug' => $submission->slug,
                'img'   => $submission->data['img']??'',
                'files' => $submission->data['files']??'',
                'status'=> $submission->status,
                'submission_url' => $submission->data['url']??$comment_url,
                'comment_url'    => $comment_url,
                'domain'         => $submission->data['domain']??'',
                'category_name'  => $category_name,
                'created_at'     => (string) $submission->created_at
            ];
        }
        $return = $submissions->toArray();
        $return['data'] = $list;
        return self::createJsonData(true,$return);
    }

}
