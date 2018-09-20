@extends('admin/public/layout')

@section('title')
    圈子管理
@endsection

@section('content')
    <section class="content-header">
        <h1>
            圈子列表
            <small>管理圈子</small>
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
                                    <a href="{{ route('admin.group.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="创建圈子"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.group.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除选中项" onclick="confirm_submit('item_form','{{  route('admin.group.destroy') }}','有文章的圈子将不会被删除,确认删除选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-10">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.group.index') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2 hidden-xs">
                                            <input type="text" class="form-control" name="user_id" placeholder="圈主" value="{{ $filter['user_id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2 hidden-xs">
                                            <input type="text" class="form-control" name="name" placeholder="圈子名称" value="{{ $filter['name'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <select class="form-control" name="status">
                                                <option value="-9">状态</option>
                                                <option value="0" @if( isset($filter['status']) && $filter['status']==0) selected @endif >未审核</option>
                                                <option value="1" @if( isset($filter['status']) && $filter['status']==1) selected @endif >已审核</option>
                                                <option value="2" @if( isset($filter['status']) && $filter['status']==2) selected @endif >已拒绝</option>
                                                <option value="3" @if( isset($filter['status']) && $filter['status']==3) selected @endif >系统圈子</option>
                                                <option value="4" @if( isset($filter['status']) && $filter['status']==4) selected @endif >已关闭</option>
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
                                        <th><input type="checkbox" class="checkbox-toggle"/></th>
                                        <th>圈子ID</th>
                                        <th>圈主ID</th>
                                        <th>圈主姓名</th>
                                        <th>圈子名称</th>
                                        <th>logo</th>
                                        <th>描述</th>
                                        <th>公开</th>
                                        <th>人数/贴子数</th>
                                        <th>创建时间</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($groups as $group)
                                        <tr>
                                            <td><input type="checkbox" value="{{ $group->id }}" name="id[]"/></td>
                                            <td>{{ $group->id }}</td>
                                            <td>{{ $group->user_id }}</td>
                                            <td>{{ $group->user->name }}</td>
                                            <td>{{ $group->name }}</td>
                                            <td><img width="100" height="100" src="{{ $group->logo }}"></td>
                                            <td>{{ $group->description }}</td>
                                            <td>{{ $group->public?'公开':'私密' }}</td>
                                            <td>{{ $group->subscribers.'/'.$group->articles }}</td>
                                            <td>{{ $group->created_at }}</td>
                                            <td><span class="label @if($group->audit_status===0) label-warning @elseif($group->audit_status===2) label-danger @elseif($group->audit_status===1) label-success @else label-success @endif">{{ trans_group_status($group->audit_status) }}</span> </td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" href="{{ route('admin.group.edit',['id'=>$group->id]) }}" data-toggle="tooltip" title="基本信息"><i class="fa fa-edit"></i></a>
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
                                    <a href="{{ route('admin.group.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="创建圈子"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.group.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除选中项" onclick="confirm_submit('item_form','{{  route('admin.group.destroy') }}','有文章的圈子将不会被删除,确认删除选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $groups->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $groups->appends($filter)->render()) !!}
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
        set_active_menu('manage_group',"{{ route('admin.group.index') }}");
    </script>
@endsection