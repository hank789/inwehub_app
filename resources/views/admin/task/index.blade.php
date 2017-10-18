@extends('admin.public.layout')
@section('title')用户任务管理@endsection
@section('content')
    <section class="content-header">
        <h1>
            用户任务管理
            <small>管理用户任务</small>
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
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="关闭选中项" onclick="confirm_submit('item_form','{{  route('admin.task.close') }}','确认关闭选中项？')"><i class="fa fa-close"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-10">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.task.index') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="uid" placeholder="用户id" value="{{ $filter['uid'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <select class="form-control" name="status">
                                                <option value="-1">--状态--</option>
                                                @foreach(trans_task_status('all') as $key => $status)
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
                                        <th>ID</th>
                                        <th>任务名称</th>
                                        <th>任务内容</th>
                                        <th>用户</th>
                                        <th>头像</th>
                                        <th>创建时间</th>
                                        <th>状态</th>
                                    </tr>
                                    @foreach($list as $task)
                                        <tr>
                                            <td><input type="checkbox" name="id[]" value="{{ $task['id'] }}"/></td>
                                            <td>{{ $task['id'] }}</td>
                                            <td>{{ $task['task_type_description'].'|'.$task['status_description'] }}</td>
                                            <td>{{ $task['description'] }}</td>
                                            <td>{{ $task['user_name'] }}</td>
                                            <td><img width="100" height="100" src="{{ $task['user_avatar_url'] }}"></td>
                                            <td>{{ timestamp_format($task['created_at']) }}</td>
                                            <td><span class="label @if($task['task_status']==0) label-warning  @else label-success @endif">{{ trans_task_status($task['task_status']) }}</span> </td>
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
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="关闭选中项" onclick="confirm_submit('item_form','{{  route('admin.task.close') }}','确认关闭选中项？')"><i class="fa fa-close"></i></button>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $tasks->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $tasks->render()) !!}
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
        set_active_menu('manage_task',"{{ route('admin.task.index') }}");
    </script>
@endsection