@extends('theme::layout.public')

@section('seo_title')修改邮箱 - {{ Setting()->get('website_name') }}@endsection

@section('content')
    <div class="row">
        <!--左侧菜单-->
        @include('theme::layout.profile_menu')

        <div id="main" class="settings col-md-10 form-horizontal">
            <h2 class="h3 post-title">修改邮箱</h2>
            <div class="row mt-30">
                <div class="col-md-8">
                    <form name="baseForm" id="base_form" action="{{ route('auth.profile.email')}}" method="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="form-group @if ($errors->first('email')) has-error @endif">
                            <label for="email" class="required control-label col-sm-3">邮箱地址</label>
                            <div class="col-sm-9">
                                <input name="email" id="email" type="text" maxlength="64"  class="form-control" value="{{ old('email',Auth()->user()->email) }}" />
                                @if ($errors->first('email'))
                                    <span class="help-block">{{ $errors->first('email') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group @if ($errors->first('captcha')) has-error @endif">
                            <label for="captcha" class="required control-label col-sm-3">验证码</label>
                            <div class="col-sm-4">
                                <input id="captcha" name="captcha" type="text" maxlength="32" placeholder="请输入下方验证码" class="form-control" value="{{ old('captcha') }}" />
                                @if ($errors->first('captcha'))
                                    <span class="help-block">{{ $errors->first('captcha') }}</span>
                                @endif
                                <div class="mt-10"><a href="javascript:void(0);" id="reloadCaptcha"><img src="{{ captcha_src()}}"></a></div>
                            </div>
                        </div>
                        <div class="form-action row">
                            <div class="col-sm-offset-3 col-sm-9">
                                <button class="btn btn-xl btn-primary" type="submit">提交</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

