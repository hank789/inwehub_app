@extends('admin/public/layout')

@section('title')
    找顾问助手用户管理
@endsection

@section('content')
    <section class="content-header">
        <h1>
            用户列表
            <small>管理微信小程序找顾问助手注册用户</small>
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
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.weapp.user.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="禁用选中项" onclick="confirm_submit('item_form','{{  route('admin.weapp.user.cancelVerify') }}','确认取消审核选中项？')"><i class="fa fa-lock"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-10">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.weapp.user.index') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2 hidden-xs">
                                            <input type="text" class="form-control" name="user_id" placeholder="UID" value="{{ $filter['user_id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2 hidden-xs">
                                            <input type="text" class="form-control" name="wechat_nickname" placeholder="微信昵称" value="{{ $filter['wechat_nickname'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <select class="form-control" name="status">
                                                <option value="-9">状态</option>
                                                    <option value="0" @if( isset($filter['status']) && $filter['status']==0) selected @endif >未审核</option>
                                                    <option value="1" @if( isset($filter['status']) && $filter['status']==1) selected @endif >已审核</option>
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
                                        <th>用户ID</th>
                                        <th>用户姓名</th>
                                        <th>微信昵称</th>
                                        <th>手机</th>
                                        <th>身份职业</th>
                                        <th>公司</th>
                                        <th>邮箱</th>
                                        <th>注册时间</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($users as $user)
                                        <tr>
                                            <td><input type="checkbox" value="{{ $user->id }}" name="id[]"/></td>
                                            <td>{{ $user->user_id }}</td>
                                            <td>{{ $user->user?$user->user->name:'' }}</td>
                                            <td>{{ $user->nickname }}</td>
                                            <td>{{ $user->user?$user->user->mobile:'' }}</td>
                                            <td>{{ $user->user?$user->user->title:'' }}</td>
                                            <td>{{ $user->user?$user->user->company:'' }}</td>
                                            <td>{{ $user->user?$user->user->email:'' }}</td>
                                            <td>{{ $user->created_at }}</td>
                                            <td><span class="label @if($user->status===0) label-danger @elseif($user->status===-1) label-default @elseif($user->status===1) label-success @endif">{{ trans_common_status($user->status) }}</span> </td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    @if ($user->user_id)
                                                        <a class="btn btn-default" href="{{ route('admin.user.edit',['id'=>$user->user_id]) }}" data-toggle="tooltip" title="基本信息"><i class="fa fa-edit"></i></a>
                                                    @endif
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
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.weapp.user.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="禁用选中项" onclick="confirm_submit('item_form','{{  route('admin.weapp.user.cancelVerify') }}','确认取消审核选中项？')"><i class="fa fa-lock"></i></button>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $users->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $users->appends($filter)->render()) !!}
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
        set_active_menu('manage_weapp_user',"{{ route('admin.weapp.user.index') }}");
    </script>
@endsection