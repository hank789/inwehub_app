@extends('admin/public/layout')
@section('title')财务数据设置@endsection
@section('content')
<section class="content-header">
    <h1>
        财务数据设置
        <small>财务相关数据设置</small>
    </h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <form role="form" name="addForm" method="POST" action="{{ route('admin.finance.setting.index') }}">
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
                                    <td>是否暂停提现</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('withdraw_suspend')) has-error @endif ">
                                            <div class="radio">
                                                <label><input type="radio" name="withdraw_suspend" value="0" @if ( Setting()->get('withdraw_suspend',0) == 0) checked @endif >正常提现</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                                <label><input type="radio" name="withdraw_suspend" value="1" @if ( Setting()->get('withdraw_suspend',0) == 1) checked @endif>暂停提现功能</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>是否自动提现</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('withdraw_auto')) has-error @endif ">
                                            <div class="radio">
                                                <label><input type="radio" name="withdraw_auto" value="0" @if ( Setting()->get('withdraw_auto',0) == 0) checked @endif >需要人工在后台审核才能提现</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                                <label><input type="radio" name="withdraw_auto" value="1" @if ( Setting()->get('withdraw_auto',0) == 1) checked @endif>系统自动提现</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>是否强制支付</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('need_pay_actual')) has-error @endif ">
                                            <div class="radio">
                                                <label><input type="radio" name="need_pay_actual" value="0" @if ( Setting()->get('need_pay_actual',0) == 0) checked @endif >非强制,表示用户不付费就可以提问</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                                <label><input type="radio" name="need_pay_actual" value="1" @if ( Setting()->get('need_pay_actual',0) == 1) checked @endif>强制付费问答</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>支付方式</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('pay_methods')) has-error @endif ">
                                            <div class="checkbox">
                                                <input type="checkbox" name="pay_method_weixin" value="1"  @if(Setting()->get('pay_method_weixin',1) == 1) checked @endif /> 微信
                                                <label><input type="checkbox" name="pay_method_ali" value="1" @if(Setting()->get('pay_method_ali',0) == 1) checked @endif /> 支付宝</label>
                                                <label><input type="checkbox" name="pay_method_iap" value="1" @if(Setting()->get('pay_method_iap',0) == 1) checked @endif /> IAP支付</label>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>付款结算周期(天)</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('pay_settlement_cycle')) has-error @endif "><input type="text" class="form-control" name="pay_settlement_cycle" value="{{ old('pay_settlement_cycle',Setting()->get('pay_settlement_cycle',5)) }}" /></div>
                                        @if($errors->has('pay_settlement_cycle')) <p class="help-block">{{ $errors->first('pay_settlement_cycle') }}</p> @endif
                                    </td>
                                </tr>

                                <tr>
                                    <td>一般用户结算手续费(0~1)</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('pay_answer_normal_fee_rate')) has-error @endif "><input type="text" class="form-control" name="pay_answer_normal_fee_rate" value="{{ old('pay_answer_normal_fee_rate',Setting()->get('pay_answer_normal_fee_rate',0.2)) }}" /></div>
                                        @if($errors->has('pay_answer_normal_fee_rate')) <p class="help-block">{{ $errors->first('pay_answer_normal_fee_rate') }}</p> @endif
                                    </td>
                                </tr>

                                <tr>
                                    <td>用户每天最大提现次数</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('withdraw_day_limit')) has-error @endif "><input type="text" class="form-control" name="withdraw_day_limit" value="{{ old('withdraw_day_limit',Setting()->get('withdraw_day_limit')) }}" /></div>
                                        @if($errors->has('withdraw_day_limit')) <p class="help-block">{{ $errors->first('withdraw_day_limit') }}</p> @endif

                                    </td>
                                </tr>

                                <tr>
                                    <td>单次最低提现金额(元)</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('withdraw_per_min_money')) has-error @endif "><input type="text" class="form-control" name="withdraw_per_min_money" value="{{ old('withdraw_per_min_money',Setting()->get('withdraw_per_min_money')) }}" /></div>
                                        @if($errors->has('withdraw_per_min_money')) <p class="help-block">{{ $errors->first('withdraw_per_min_money') }}</p> @endif

                                    </td>
                                </tr>

                                <tr>
                                    <td>单次最高提现金额(元)</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('withdraw_per_max_money')) has-error @endif "><input type="text" class="form-control" name="withdraw_per_max_money" value="{{ old('withdraw_per_max_money',Setting()->get('withdraw_per_max_money')) }}" /></div>
                                        @if($errors->has('withdraw_per_max_money')) <p class="help-block">{{ $errors->first('withdraw_per_max_money') }}</p> @endif

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
    set_active_menu('finance',"{{ route('admin.finance.setting.index') }}");
</script>
@endsection