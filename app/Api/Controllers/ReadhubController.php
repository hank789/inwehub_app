<?php

namespace App\Api\Controllers;

use App\Exceptions\ApiException;
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
        if ($uuid) {
            $user = User::where('uuid',$uuid)->first();
            if (!$user) {
                throw new ApiException(ApiException::BAD_REQUEST);
            }
        } else {
            $user = $request->user();
        }

        $query = Submission::where('user_id',$user->id);

        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }
        $submissions = $query->orderBy('id','DESC')->paginate(Config::get('inwehub.api_data_page_size'));

        $list = [];
        foreach($submissions as $submission){
            $comment_url = '/c/'.$submission->category_id.'/'.$submission->slug;

            $list[] = [
                'id' => $submission->id,
                'type' => $submission->type,
                'title' => $submission->formatTitle(),
                'img'   => $submission->data['img']??'',
                'submission_url' => $submission->data['url']??$comment_url,
                'comment_url'    => $comment_url,
                'domain'         => $submission->data['domain']??'',
                'category_name'  => $submission->category_name,
                'created_at'     => (string) $submission->created_at
            ];
        }
        return self::createJsonData(true,$list);
    }

}
