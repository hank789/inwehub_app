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
 *
 * @package App\Models\Readhub
 * @mixin \Eloquent
 * @property int $id
 * @property string|null $uuid
 * @property int $user_level
 * @property string $username
 * @property string|null $name
 * @property string|null $website
 * @property string|null $location
 * @property string $avatar
 * @property string $color
 * @property string|null $bio
 * @property int $active
 * @property int $confirmed
 * @property string|null $email
 * @property array $settings
 * @property array $info
 * @property int $verified
 * @property int $submission_karma
 * @property int $comment_karma
 * @property string $password
 * @property string|null $deleted_at
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereCommentKarma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereConfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereSubmissionKarma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereUserLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereWebsite($value)
 * @property int $is_expert
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\ReadHubUser whereIsExpert($value)
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
        'id','username', 'name','uuid', 'email', 'password', 'location', 'bio',
        'website', 'settings', 'color', 'avatar', 'confirmed',
        'active', 'info', 'comment_karma', 'submission_karma','is_expert'
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
        self::syncUser($user);
    }

    public static function syncUser(User $user){
        // read站点同步注册用户
        $exist = ReadHubUser::where('uuid','=',$user->uuid)->first();

        if ($exist) {
            $exist->username = $user->name;
            $exist->name = $user->name;
            $exist->password = $user->password;
            $exist->bio = $user->description;
            if ($user->email) {
                $exist->email = $user->email;
            }
            $exist->avatar = $user->avatar;
            $exist->user_level = $user->userData->user_level;
            $exist->is_expert = ($user->authentication && $user->authentication->status === 1) ? 1 : 0;
            $exist->save();
        } else {
            ReadHubUser::create([
                'id'       => $user->id,
                'username' => $user->name,
                'uuid'     => $user->uuid,
                'name'     => $user->name,
                'active'   => 1,
                'confirmed' => 1,
                'verfied'   => 1,
                'password' => $user->password,
                'avatar'   => $user->avatar,
                'is_expert' => ($user->authentication && $user->authentication->status === 1) ? 1 : 0,
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
            // 设置默认订阅频道
            Subscription::create([
                'user_id' => $user->id,
                'category_id' => 3
            ]);

        }
    }

}