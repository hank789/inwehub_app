@extends('admin/public/layout')
@section('title')首页运营数据设置@endsection
@section('content')
<section class="content-header">
    <h1>
        首页运营数据设置
        <small>app首页相关数据设置</small>
    </h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <form role="form" name="addForm" method="POST" action="{{ route('admin.operate.home_data') }}">
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
                                    <td>行业专家数量</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('operate_expert_number')) has-error @endif "><input type="text" class="form-control" name="operate_expert_number" value="{{ old('operate_expert_number',Setting()->get('operate_expert_number')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>平均应答分钟</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('operate_average_answer_minute')) has-error @endif "><input type="text" class="form-control" name="operate_average_answer_minute" value="{{ old('operate_average_answer_minute',Setting()->get('operate_average_answer_minute')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>行业数量</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('operate_industry_number')) has-error @endif "><input type="text" class="form-control" name="operate_industry_number" value="{{ old('operate_industry_number',Setting()->get('operate_industry_number')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>首页推荐图片</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('operate_header_image_url')) has-error @endif "><input type="text" class="form-control" name="operate_header_image_url" value="{{ old('operate_header_image_url',Setting()->get('operate_header_image_url')) }}" /></div>
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
    set_active_menu('operations',"{{ route('admin.operate.home_data') }}");
</script>
@endsection