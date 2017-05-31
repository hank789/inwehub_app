@extends('admin/public/layout')
@section('title')问答设置@endsection
@section('content')
<section class="content-header">
    <h1>
        问答设置
        <small>问答设置</small>
    </h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <form role="form" name="addForm" method="POST" action="{{ route('admin.setting.answer') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped dataTable text-center">
                                <thead>
                                <tr role="row">
                                    <th>参数</th>
                                    <th>数值</th>
                                </tr>
                                </thead>

                                <tbody>
                                <tr>
                                    <td>强制邀请回答者为专家</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('is_inviter_must_expert')) has-error @endif ">
                                            <div class="radio">
                                                <label><input type="radio" name="is_inviter_must_expert" value="0" @if ( Setting()->get('is_inviter_must_expert',1) == 0) checked @endif >否</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                                <label><input type="radio" name="is_inviter_must_expert" value="1" @if ( Setting()->get('is_inviter_must_expert',1) == 1) checked @endif>是</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>超时未处理通知专家(分钟)</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('alert_minute_expert_unconfirm_question')) has-error @endif "><input type="text" class="form-control" name="alert_minute_expert_unconfirm_question" value="{{ old('alert_minute_expert_unconfirm_question',Setting()->get('alert_minute_expert_unconfirm_question',10)) }}" /></div>
                                        @if($errors->has('alert_minute_expert_unconfirm_question')) <p class="help-block">{{ $errors->first('alert_minute_expert_unconfirm_question') }}</p> @endif
                                    </td>
                                </tr>

                                <tr>
                                    <td>离承诺时间多久告警专家(分钟)</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('alert_minute_expert_over_question_promise_time')) has-error @endif "><input type="text" class="form-control" name="alert_minute_expert_over_question_promise_time" value="{{ old('alert_minute_expert_over_question_promise_time',Setting()->get('alert_minute_expert_over_question_promise_time',10)) }}" /></div>
                                        @if($errors->has('alert_minute_expert_over_question_promise_time')) <p class="help-block">{{ $errors->first('alert_minute_expert_over_question_promise_time') }}</p> @endif
                                    </td>
                                </tr>

                                <tr>
                                    <td>专家超时未处理告警运营(分钟)</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('alert_minute_operator_question_unconfirm')) has-error @endif "><input type="text" class="form-control" name="alert_minute_operator_question_unconfirm" value="{{ old('alert_minute_operator_question_unconfirm',Setting()->get('alert_minute_operator_question_unconfirm',10)) }}" /></div>
                                        @if($errors->has('alert_minute_operator_question_unconfirm')) <p class="help-block">{{ $errors->first('alert_minute_operator_question_unconfirm') }}</p> @endif
                                    </td>
                                </tr>

                                <tr>
                                    <td>运营超时未分配告警(分钟)</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('alert_minute_operator_question_uninvite')) has-error @endif "><input type="text" class="form-control" name="alert_minute_operator_question_uninvite" value="{{ old('alert_minute_operator_question_uninvite',Setting()->get('alert_minute_operator_question_uninvite',10)) }}" /></div>
                                        @if($errors->has('alert_minute_operator_question_uninvite')) <p class="help-block">{{ $errors->first('alert_minute_operator_question_uninvite') }}</p> @endif
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
    set_active_menu('global',"{{ route('admin.setting.answer') }}");
</script>
@endsection