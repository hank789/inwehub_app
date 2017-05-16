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
                                    <td>是否自动提现</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('withdraw_auto')) has-error @endif ">
                                            <input type="text" class="form-control" name="withdraw_auto" value="{{ old('withdraw_auto',Setting()->get('withdraw_auto')) }}" />
                                            <span class="text-muted">0:需要人工在后台审核才能提现;1:系统自动提现</span>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>用户每天最大提现次数</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('withdraw_day_limit')) has-error @endif "><input type="text" class="form-control" name="withdraw_day_limit" value="{{ old('withdraw_day_limit',Setting()->get('withdraw_day_limit')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>单次最低提现金额(元)</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('withdraw_per_min_money')) has-error @endif "><input type="text" class="form-control" name="withdraw_per_min_money" value="{{ old('withdraw_per_min_money',Setting()->get('withdraw_per_min_money')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>单次最高提现金额(元)</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('withdraw_per_max_money')) has-error @endif "><input type="text" class="form-control" name="withdraw_per_max_money" value="{{ old('withdraw_per_max_money',Setting()->get('withdraw_per_max_money')) }}" /></div>
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