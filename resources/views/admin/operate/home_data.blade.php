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
                                    <td>推荐专家姓名</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('recommend_expert_name')) has-error @endif "><input type="text" class="form-control" name="recommend_expert_name" value="{{ old('recommend_expert_name',Setting()->get('recommend_expert_name')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>推荐专家描述</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('recommend_expert_description')) has-error @endif "><input type="text" class="form-control" name="recommend_expert_description" value="{{ old('recommend_expert_description',Setting()->get('recommend_expert_description')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>推荐专家系统用户id</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('recommend_expert_uid')) has-error @endif "><input type="text" class="form-control" name="recommend_expert_uid" value="{{ old('recommend_expert_uid',Setting()->get('recommend_expert_uid')) }}" /></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>推荐专家头像地址</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('recommend_expert_avatar_url')) has-error @endif "><input type="text" class="form-control" name="recommend_expert_avatar_url" value="{{ old('recommend_expert_avatar_url',Setting()->get('recommend_expert_avatar_url')) }}" /></div>
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