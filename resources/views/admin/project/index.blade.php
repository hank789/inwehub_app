@extends('admin/public/layout')
@section('title')企业需求管理@endsection
@section('content')
    <section class="content-header">
        <h1>
            企业需求管理
            <small>管理企业需求</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-3">
                                <div class="btn-group">
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.project.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="审核不通过" onclick="confirm_submit('item_form','{{  route('admin.project.destroy') }}','确认禁用选中项？')"><i class="fa fa-lock"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-9">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.project.index') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-3">
                                            <select class="form-control" name="apply_status">
                                                <option value="-1">--状态--</option>
                                                @foreach(trans_company_apply_status('all') as $key => $status)
                                                    <option value="{{ $key }}" @if( isset($filter['apply_status']) && $filter['apply_status']==$key) selected @endif >{{ $status }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-xs-2 hidden-xs">
                                            <input type="text" class="form-control" name="user_id" placeholder="UID" value="{{ $filter['user_id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
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
                                        <th>项目名称</th>
                                        <th>发布者</th>
                                        <th>项目类型</th>
                                        <th>项目阶段</th>
                                        <th>状态</th>
                                        <th>更新时间</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($projects as $project)
                                        <tr>
                                            <td><input type="checkbox" name="id[]" value="{{ $project->id }}"/></td>
                                            <td>{{ $project->id }}</td>
                                            <td>{{ $project->project_name }}</td>
                                            <td>{{ $project->user->name }}</td>
                                            <td>{{ trans_project_type($project->project_type) }}</td>
                                            <td>{{ trans_project_stage($project->project_stage) }}</td>
                                            <td><span class="label @if($project->status===1) label-default  @elseif($project->status===2) label-success @else label-warning  @endif">{{ trans_company_apply_status($project->status) }}</span> </td>
                                            <td>{{ timestamp_format($project->updated_at) }}</td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" target="_blank" href="{{ route('admin.project.detail',['id'=>$project->id]) }}" data-toggle="tooltip" title="查看"><i class="fa fa-eye"></i></a>
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
                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $projects->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $projects->render()) !!}
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
        set_active_menu('manage_project',"{{ route('admin.project.index') }}");
    </script>
@endsection