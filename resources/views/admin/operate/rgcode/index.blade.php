@extends('admin.public.layout')
@section('title')邀请码管理@endsection
@section('content')
    <section class="content-header">
        <h1>
            邀请码管理
            <small>管理邀请码</small>
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
                                    <a href="{{ route('admin.operate.rgcode.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="添加邀请码"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.operate.rgcode.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="禁用选中项" onclick="confirm_submit('item_form','{{  route('admin.operate.rgcode.destroy') }}','确认禁用选中项？')"><i class="fa fa-lock"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-10">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.operate.rgcode.index') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="keyword" placeholder="邀请对象" value="{{ $filter['keyword'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="code" placeholder="邀请码" value="{{ $filter['code'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <select class="form-control" name="status">
                                                <option value="-1">--状态--</option>
                                                @foreach(trans_rgcode_status('all') as $key => $status)
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
                                        <th>邀请对象</th>
                                        <th>邀请码</th>
                                        <th>注册用户</th>
                                        <th>失效时间</th>
                                        <th>添加时间</th>
                                        <th>创建者</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($codes as $code)
                                        <tr>
                                            <td><input type="checkbox" name="id[]" value="{{ $code->id }}"/></td>
                                            <td>{{ $code->keyword }}</td>
                                            <td>{{ $code->code }}</td>
                                            <td>{{ $code->getRegisterUser()->name }}</td>
                                            <td>{{ $code->expired_at }}</td>
                                            <td>{{ timestamp_format($code->created_at) }}</td>
                                            <td>{{ $code->getRecommendUser()->name }}</td>
                                            <td><span class="label @if($code->status===0) label-danger  @else label-success @endif">{{ trans_rgcode_status($code->status) }}</span> </td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" href="{{ route('admin.operate.rgcode.edit',['id'=>$code->id]) }}" data-toggle="tooltip" title="编辑"><i class="fa fa-edit"></i></a>
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
                                    <a href="{{ route('admin.operate.rgcode.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="添加邀请码"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.operate.rgcode.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="禁用选中项" onclick="confirm_submit('item_form','{{  route('admin.operate.rgcode.destroy') }}','确认禁用选中项？')"><i class="fa fa-lock"></i></button>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $codes->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $codes->appends($filter)->render()) !!}
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
        set_active_menu('operations',"{{ route('admin.operate.rgcode.index') }}");
    </script>
@endsection