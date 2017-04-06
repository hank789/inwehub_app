<?php

namespace App\Api\Requests\Auth;

use Config;
use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('boilerplate.forgot_password.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
