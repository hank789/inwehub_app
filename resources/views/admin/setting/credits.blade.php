@extends('admin/public/layout')
@section('title')积分设置@endsection
@section('content')
<section class="content-header">
    <h1>
        积分设置
        <small>网站用户积分策略设置</small>
    </h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <form role="form" name="addForm" method="POST" action="{{ route('admin.setting.credits') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="box-body">
                        <div class="alert alert-info" role="alert">经验和金币都可以设置为负数，0，或者正数，负数表示扣分</div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped dataTable text-center">
                                <thead>
                                <tr role="row">
                                    <th>用户行为</th>
                                    <th>成长值</th>
                                    <th>哈币数</th>
                                </tr>
                                </thead>

                                <tbody>
                                <tr>
                                    <td>用户注册获得</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('credits_register')) has-error @endif "><input type="text" class="form-control" name="credits_register" value="{{ old('credits_register',Setting()->get('credits_register')) }}" /></div>
                                    </td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('coins_register')) has-error @endif "><input type="text" class="form-control" name="coins_register" value="{{ old('coins_register',Setting()->get('coins_register')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>上传头像</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('credits_upload_avatar')) has-error @endif "><input type="text" class="form-control" name="credits_upload_avatar" value="{{ old('credits_upload_avatar',Setting()->get('credits_upload_avatar')) }}" /></div>
                                    </td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('coins_upload_avatar')) has-error @endif "><input type="text" class="form-control" name="coins_upload_avatar" value="{{ old('coins_upload_avatar',Setting()->get('coins_upload_avatar')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>简历填写完成</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('credits_user_info_complete')) has-error @endif "><input type="text" class="form-control" name="credits_user_info_complete" value="{{ old('credits_user_info_complete',Setting()->get('credits_user_info_complete')) }}" /></div>
                                    </td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('coins_user_info_complete')) has-error @endif "><input type="text" class="form-control" name="coins_user_info_complete" value="{{ old('coins_user_info_complete',Setting()->get('coins_user_info_complete')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>每日签到</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('credits_user_sign_daily')) has-error @endif "><input type="text" class="form-control" name="credits_user_sign_daily" value="{{ old('credits_user_sign_daily',Setting()->get('credits_user_sign_daily')) }}" /></div>
                                    </td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('coins_user_sign_daily')) has-error @endif "><input type="text" class="form-control" name="coins_user_sign_daily" value="{{ old('coins_user_sign_daily',Setting()->get('coins_user_sign_daily')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>每日登陆</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('credits_login')) has-error @endif "><input type="text" class="form-control" name="credits_login" value="{{ old('credits_login',Setting()->get('credits_login')) }}" /></div>
                                    </td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('coins_login')) has-error @endif "><input type="text" class="form-control" name="coins_login" value="{{ old('coins_login',Setting()->get('coins_login')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>完成首次提问</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('credits_first_ask')) has-error @endif "><input type="text" class="form-control" name="credits_first_ask" value="{{ old('credits_first_ask',Setting()->get('credits_first_ask')) }}" /></div>
                                    </td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('coins_first_ask')) has-error @endif "><input type="text" class="form-control" name="coins_first_ask" value="{{ old('coins_first_ask',Setting()->get('coins_first_ask')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>每次提问</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('credits_ask')) has-error @endif "><input type="text" class="form-control" name="credits_ask" value="{{ old('credits_ask',Setting()->get('credits_ask')) }}" /></div>
                                    </td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('coins_ask')) has-error @endif "><input type="text" class="form-control" name="coins_ask" value="{{ old('coins_ask',Setting()->get('coins_ask')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>完成首次回答</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('credits_first_answer')) has-error @endif "><input type="text" class="form-control" name="credits_first_answer" value="{{ old('credits_first_answer',Setting()->get('credits_first_answer')) }}" /></div>
                                    </td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('coins_first_answer')) has-error @endif "><input type="text" class="form-control" name="coins_first_answer" value="{{ old('coins_first_answer',Setting()->get('coins_first_answer')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>每次回答</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('credits_answer')) has-error @endif "><input type="text" class="form-control" name="credits_answer" value="{{ old('credits_answer',Setting()->get('credits_answer')) }}" /></div>
                                    </td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('coins_answer')) has-error @endif "><input type="text" class="form-control" name="coins_answer" value="{{ old('coins_answer',Setting()->get('coins_answer')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>超出承诺时间未回答每小时(扣分)</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('credits_answer_over_promise_time_hourly')) has-error @endif "><input type="text" class="form-control" name="credits_answer_over_promise_time_hourly" value="{{ old('credits_answer_over_promise_time_hourly',Setting()->get('credits_answer_over_promise_time_hourly')) }}" /></div>
                                    </td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('coins_answer_over_promise_time_hourly')) has-error @endif "><input type="text" class="form-control" name="coins_answer_over_promise_time_hourly" value="{{ old('coins_answer_over_promise_time_hourly',Setting()->get('coins_answer_over_promise_time_hourly')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>超出承诺时间未回答最多扣</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('credits_answer_over_promise_time_max')) has-error @endif "><input type="text" class="form-control" name="credits_answer_over_promise_time_max" value="{{ old('credits_answer_over_promise_time_max',Setting()->get('credits_answer_over_promise_time_max')) }}" /></div>
                                    </td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('coins_answer_over_promise_time_max')) has-error @endif "><input type="text" class="form-control" name="coins_answer_over_promise_time_max" value="{{ old('coins_answer_over_promise_time_max',Setting()->get('coins_answer_over_promise_time_max')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>优质提问</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('credits_ask_good')) has-error @endif "><input type="text" class="form-control" name="credits_ask_good" value="{{ old('credits_ask_good',Setting()->get('credits_ask_good')) }}" /></div>
                                    </td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('coins_ask_good')) has-error @endif "><input type="text" class="form-control" name="coins_ask_good" value="{{ old('coins_ask_good',Setting()->get('coins_ask_good')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>优质回答</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('credits_answer_good')) has-error @endif "><input type="text" class="form-control" name="credits_answer_good" value="{{ old('credits_answer_good',Setting()->get('credits_answer_good')) }}" /></div>
                                    </td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('coins_answer_good')) has-error @endif "><input type="text" class="form-control" name="coins_answer_good" value="{{ old('coins_answer_good',Setting()->get('coins_answer_good')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>每邀请一位好友并激活</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('credits_invite_user')) has-error @endif "><input type="text" class="form-control" name="credits_invite_user" value="{{ old('credits_invite_user',Setting()->get('credits_invite_user')) }}" /></div>
                                    </td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('coins_invite_user')) has-error @endif "><input type="text" class="form-control" name="credits_invite_user" value="{{ old('coins_invite_user',Setting()->get('coins_invite_user')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>完成专家认证</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('credits_expert_valid')) has-error @endif "><input type="text" class="form-control" name="credits_expert_valid" value="{{ old('credits_expert_valid',Setting()->get('credits_expert_valid')) }}" /></div>
                                    </td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('coins_expert_valid')) has-error @endif "><input type="text" class="form-control" name="coins_expert_valid" value="{{ old('coins_expert_valid',Setting()->get('coins_expert_valid')) }}" /></div>
                                    </td>
                                </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">保存</button>
                        <button type="reset" class="btn btn-success">重置</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</section>
@endsection
@section('script')
<script type="text/javascript">
    set_active_menu('global',"{{ route('admin.setting.credits') }}");
</script>
@endsection