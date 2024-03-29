@extends('admin/public/layout')
@section('title') 积分充值 @endsection
@section('content')
    <section class="content-header">
        <h1>
            积分管理
            <small>添加贡献值、成长值</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-default">
                    <form role="form" name="addForm" method="POST"  action="{{ route('admin.credit.store') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group @if($errors->has('user_id')) has-error @endif">
                                <label>操作用户UID</label>
                                <input type="text" name="user_id" class="form-control " placeholder="操作用户的UID" value="{{ old('user_id','') }}">
                                @if($errors->has('user_id')) <p class="help-block">{{ $errors->first('user_id') }}</p> @endif
                            </div>
                            <div class="form-group">
                                <label>操作类型</label>
                                <span class="text-muted">(根据操作类型确定是加/减 贡献值或成长值)</span>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="action" value="reward_user" @if( old('action','reward_user') == 'reward_user')checked @endif /> 奖励
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="action" value="punish_user" @if( old('action','reward_user') == 'punish_user')checked @endif /> 惩罚
                                    </label>
                                </div>
                            </div>
                            <div class="form-group @if($errors->has('credits')) has-error @endif">
                                <label>备注</label>
                                <span class="text-muted">(为啥加分)</span>
                                <input type="text" name="source_subject" class="form-control " placeholder="备注" value="{{ old('source_subject') }}">
                                @if($errors->has('source_subject')) <p class="help-block">{{ $errors->first('source_subject') }}</p> @endif
                            </div>
                            <div class="form-group @if($errors->has('coins')) has-error @endif">
                                <label>贡献值</label>
                                <span class="text-muted">(只能是正整数，0为不进行修改)</span>
                                <input type="text" name="coins" class="form-control " placeholder="要操作的贡献值" value="{{ old('coins',0) }}">
                                @if($errors->has('coins')) <p class="help-block">{{ $errors->first('coins') }}</p> @endif
                            </div>
                            <div class="form-group @if($errors->has('credits')) has-error @endif">
                                <label>成长值</label>
                                <span class="text-muted">(只能是正整数，0为不进行修改)</span>
                                <input type="text" name="credits" class="form-control " placeholder="要操作的成长值" value="{{ old('credits',0) }}">
                                @if($errors->has('credits')) <p class="help-block">{{ $errors->first('credits') }}</p> @endif
                            </div>
                            <div class="form-group @if($errors->has('credits')) has-error @endif">
                                <label>账户金额</label>
                                <span class="text-muted">(只能是正整数，0为不进行修改)</span>
                                <input type="text" name="money" class="form-control " placeholder="要操作的钱包金额" value="{{ old('money',0) }}">
                                @if($errors->has('money')) <p class="help-block">{{ $errors->first('money') }}</p> @endif
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">提交</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script type="text/javascript">
        $(function(){
            set_active_menu('credit',"{{ route('admin.credit.index') }}");
        });
    </script>
@endsection
