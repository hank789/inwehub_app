@extends('admin/public/layout')

@section('title')企业服务@endsection

@section('content')
    <section class="content-header">
        <h1>企业服务</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-3">
                                <div class="btn-group">
                                    <a href="{{ route('admin.company.service.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="添加新服务"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.company.service.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="审核不通过选中项" onclick="confirm_submit('item_form','{{  route('admin.company.service.unverify') }}','确认取消推荐选中项？')"><i class="fa fa-lock"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除推荐" onclick="confirm_submit('item_form','{{  route('admin.company.service.destroy',['id'=>0]) }}', '确认删除选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-9">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.company.service.index') }}">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-4">
                                            <input type="text" class="form-control" name="word" placeholder="关键词" value="{{ $filter['word'] or '' }}"/>
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
                                        <th><input type="checkbox" class="checkbox-toggle"/></th>
                                        <th>ID</th>
                                        <th>标题</th>
                                        <th>幻灯片图片</th>
                                        <th>列表图片</th>
                                        <th>排序</th>
                                        <th>审核状态</th>
                                        <th>更新时间</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($services as $item)
                                        <tr>
                                            <td><input type="checkbox" value="{{ $item->id }}" name="ids[]"/></td>
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->title }}</td>
                                            <td>
                                                <img width="100" height="100" src="{{ $item->img_url_slide }}">
                                            </td>
                                            <td>
                                                <img width="100" height="100" src="{{ $item->img_url_list }}">
                                            </td>
                                            <td>{{ $item->sort }}</td>
                                            <td><span class="label @if($item->audit_status===0) label-danger  @else label-success @endif">{{ trans_authentication_status($item->audit_status) }}</span> </td>
                                            <td>{{ $item->updated_at }}</td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" href="{{ route('admin.company.service.edit',['id'=>$item->id]) }}" data-toggle="tooltip" title="编辑"><i class="fa fa-edit"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </form>
                    </div>
                    <div class="box-footer clearfix">
                        {!! str_replace('/?', '?', $services->appends($filter)->render()) !!}
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('script')
    <script type="text/javascript">
        set_active_menu('manage_company',"{{ route('admin.company.service.index') }}");
    </script>
@endsection