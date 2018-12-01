<?php namespace App\Services;

use App\Models\Pay\UserMoney;
use App\Models\User;
use App\Models\UserData;
use Carbon\Carbon;
use Validator;
use Ramsey\Uuid\Uuid;

class Registrar {

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:100',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    public function create(array $data)
    {
        $user =  User::create([
            'uuid' => gen_user_uuid(),
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'mobile' => $data['mobile'],
            'rc_uid' => $data['rc_uid']??0,
            'rc_code'=> User::genRcCode(),
            'title'  => $data['title']??'',
            'company' => $data['company']??'',
            'gender' => 0,
            'password' => bcrypt($data['password']),
            'status' => $data['status'],
            'avatar' => config('image.user_default_avatar'),
            'source' => $data['source']??0,
            'info_complete_percent' => 10,
            'site_notifications' => [],
        ]);

        if($user){
            UserData::create([
                'user_id' => $user->id,
                'coins' => 0,
                'credits' => 0,
                'registered_at' => Carbon::now(),
                'last_visit' => Carbon::now(),
                'last_login_ip' => $data['visit_ip']??'',
            ]);
            UserMoney::create([
                'user_id' => $user->id,
                'total_money' => 0,
                'settlement_money' => 0
            ]);
        }

        return $user;
    }


}
