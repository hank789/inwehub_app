@extends('admin.public.layout')
@section('title')App版本管理@endsection
@section('content')
    <section class="content-header">
        <h1>
            App版本管理
            <small>管理App版本</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-2">
                                <div class="btn-group">
                                    <a href="{{ route('admin.appVersion.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="创建新版本"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.appVersion.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="禁用选中项" onclick="confirm_submit('item_form','{{  route('admin.appVersion.destroy') }}','确认禁用选中项？')"><i class="fa fa-lock"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-10">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.appVersion.index') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="app_version" placeholder="版本号" value="{{ $filter['app_version'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <select class="form-control" name="status">
                                                <option value="-1">--状态--</option>
                                                @foreach(trans_app_version_status('all') as $key => $status)
                                                    <option value="{{ $key }}" @if( isset($filter['status']) && $filter['status']==$key) selected @endif >{{ $status }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-xs-1">
                                            <button type="submit" class="btn btn-primary">搜索</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-body  no-padding">
                        <form name="itemForm" id="item_form" method="POST">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <tr>
                                        <th><input type="checkbox" class="checkbox-toggle" /></th>
                                        <th>版本号</th>
                                        <th>是否ios强更</th>
                                        <th>是否android强更</th>
                                        <th>发布者</th>
                                        <th>时间</th>
                                        <th>包地址</th>
                                        <th>更新内容</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($versions as $version)
                                        <tr>
                                            <td><input type="checkbox" name="id[]" value="{{ $version->id }}"/></td>
                                            <td>{{ $version->app_version }}</td>
                                            <td>{{ $version->is_ios_force }}</td>
                                            <td>{{ $version->is_android_force }}</td>
                                            <td>{{ $version->user_id }}</td>
                                            <td>{{ timestamp_format($version->created_at) }}</td>
                                            <td>{{ $version->package_url }}</td>
                                            <td>{{ $version->update_msg }}</td>
                                            <td><span class="label @if($version->status===0) label-danger  @else label-success @endif">{{ trans_app_version_status($version->status) }}</span> </td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" href="{{ route('admin.appVersion.edit',['id'=>$version->id]) }}" data-toggle="tooltip" title="编辑"><i class="fa fa-edit"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </form>
                    </div>
                    <div class="box-footer clearfix">
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="btn-group">
                                    <a href="{{ route('admin.appVersion.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="创建新版本"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.appVersion.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="禁用选中项" onclick="confirm_submit('item_form','{{  route('admin.appVersion.destroy') }}','确认禁用选中项？')"><i class="fa fa-lock"></i></button>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $versions->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $versions->render()) !!}
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </section>

@endsection

@section('script')
    <script type="text/javascript">
        set_active_menu('operations',"{{ route('admin.appVersion.index') }}");
    </script>
@endsection