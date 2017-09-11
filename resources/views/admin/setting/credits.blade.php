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
                                    <th>贡献值</th>
                                </tr>
                                </thead>

                                <tbody>
                                @foreach(\App\Models\Credit::$creditSetting as $key=>$setting)
                                    <tr>
                                        <td>{{ $setting['backend_label'] }}</td>
                                        <td>
                                            <div class="col-md-4 col-md-offset-4 @if ($errors->has('credits_'.$key)) has-error @endif "><input type="text" class="form-control" name="{{ 'credits_'.$key }}" value="{{ old('credits_'.$key,Setting()->get('credits_'.$key)) }}" /></div>
                                        </td>
                                        <td>
                                            <div class="col-md-4 col-md-offset-4 @if ($errors->has('coins_'.$key)) has-error @endif "><input type="text" class="form-control" name="{{ 'coins_'.$key }}" value="{{ old('coins_'.$key,Setting()->get('coins_'.$key)) }}" /></div>
                                        </td>
                                    </tr>
                                @endforeach

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
    set_active_menu('credit',"{{ route('admin.setting.credits') }}");
</script>
@endsection