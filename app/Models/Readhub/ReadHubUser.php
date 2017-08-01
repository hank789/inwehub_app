<?php namespace App\Models\Readhub;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @author: wanghui
 * @date: 2017/8/1 上午10:46
 * @email: wanghui@yonglibao.com
 */

/**
 * Class ReadHubUser
 * @package App\Models\Readhub
 * @mixin \Eloquent
 */
class ReadHubUser extends Model {

    protected $table = 'users';

    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    protected $connection = 'inwehub_read';

    protected $fillable = [
        'username', 'name','uuid', 'email', 'password', 'location', 'bio',
        'website', 'settings', 'color', 'avatar', 'confirmed',
        'active', 'info', 'comment_karma', 'submission_karma',
    ];

    protected $casts = [
        'settings' => 'json',
        'info'     => 'json',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'deleted_at', 'email', 'settings', 'verified', 'active',
    ];


    public static function initUser(User $user){
        // read站点同步注册用户
        $username = $user->name;
        ReadHubUser::create([
            'username' => $username,
            'uuid'     => $user->uuid,
            'name'     => $user->name,
            'active'   => 1,
            'confirmed' => 1,
            'verfied'   => 1,
            'password' => $user->password,
            'avatar'   => $user->getAvatarUrl(),
            'info' => [
                'website' => null,
                'twitter' => null,
            ],
            'settings'  => [
                'font'                          => 'Lato',
                'sidebar_color'                 => 'Gray',
                'nsfw'                          => false,
                'nsfw_media'                    => false,
                'notify_submissions_replied'    => true,
                'notify_comments_replied'       => true,
                'notify_mentions'               => true,
                'exclude_upvoted_submissions'   => false,
                'exclude_downvoted_submissions' => true,
                'submission_small_thumbnail'    => true,
            ],
        ]);
    }

    public static function syncUser(User $user){
        // read站点同步注册用户
        $exist = ReadHubUser::where('uuid','=',$user->uuid)->first();

        if ($exist) {
            $exist->username = $user->name;
            $exist->name = $user->name;
            $exist->password = $user->password;
            $exist->bio = $user->description;
            $exist->email = $user->email;
            $exist->avatar = $user->avatar;
            $exist->save();
        }
    }

}