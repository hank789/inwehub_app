<?php

namespace App\Api\Requests\Auth;

use Dingo\Api\Http\FormRequest;
use Config;

class LoginRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('boilerplate.login.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
