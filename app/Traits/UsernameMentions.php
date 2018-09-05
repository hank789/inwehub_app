<?php namespace App\Traits;
use App\Models\Comment;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\Readhub\UsernameCommentMentioned;
use App\Notifications\Readhub\UsernameSubmissionMentioned;

/**
 * @author: wanghui
 * @date: 2017/11/13 下午6:53
 * @email: hank.huiwang@gmail.com
 */

trait UsernameMentions
{
    public function handleCommentMentions(Comment $comment)
    {
        if (!preg_match_all('/@([\S]+)/', $comment->body, $mentionedUsernames)) {
            return;
        }

        foreach ($mentionedUsernames[1] as $key => $username) {
            // set a limit so they can't just mention the whole website! lol
            if ($key === 5) {
                return;
            }

            if ($user = User::where('name',$username)->first()) {
                $user->notify(new UsernameCommentMentioned($user->id,$comment));
            }
        }
    }

    public function handleSubmissionMentions(Submission $submission,$members = []){
        $mentions = $submission->data['mentions'];
        if (empty($mentions)) return [];
        $notified_uids = [];
        foreach ($mentions as $uid) {
            if ($uid == $submission->user_id) continue;
            if ($members && !in_array($uid,$members)) continue;
            $notified_uids[$uid] = $uid;
            $user = User::find($uid);
            $user->notify(new UsernameSubmissionMentioned($user->id,$submission));
        }
        return $notified_uids;
    }
}