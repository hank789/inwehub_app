@extends('admin/public/layout')

@section('title')
    用户管理
@endsection

@section('content')
    <section class="content-header">
        <h1>
            用户列表
            <small>显示当前系统的所有注册用户</small>
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
                                    <a href="{{ route('admin.user.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="创建新用户"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.user.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="禁用选中项" onclick="confirm_submit('item_form','{{  route('admin.user.destroy') }}','确认禁用选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-9">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.user.index') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2 hidden-xs">
                                            <input type="text" class="form-control" name="user_id" placeholder="UID" value="{{ $filter['user_id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-3 hidden-xs">
                                            <input type="text" class="form-control" name="word" placeholder="用户名|邮箱" value="{{ $filter['word'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <select class="form-control" name="status">
                                                <option value="-9">不选择</option>
                                                @foreach(trans_common_status('all') as $key => $status)
                                                    <option value="{{ $key }}" @if( isset($filter['status']) && $filter['status']==$key) selected @endif >{{ $status }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-xs-3 hidden-xs">
                                            <input type="text" name="date_range" id="date_range" class="form-control" placeholder="时间范围" value="{{ $filter['date_range'] or '' }}" />
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
                                        <th>邮箱</th>
                                        <th>手机</th>
                                        <th>身份职业</th>
                                        <th>地区</th>
                                        <th>企业用户</th>
                                        <th>问题数</th>
                                        <th>回答数</th>
                                        <th>档案完整度(%)</th>
                                        <th>账户余额</th>
                                        <th>成长值</th>
                                        <th>贡献值</th>
                                        <th>注册时间</th>
                                        <th>注册来源</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($users as $user)
                                        <tr>
                                            <td><input type="checkbox" value="{{ $user->id }}" name="id[]"/></td>
                                            <td>{{ $user->id }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->mobile }}</td>
                                            <td>{{ $user->title }}</td>
                                            <td>{{ get_province_name($user->province) }} - {{ get_city_name($user->province, $user->city) }}</td>
                                            <td>{{ $user->userData->is_company ? '是':'否' }}</td>
                                            <td>{{ $user->userData->questions }}</td>
                                            <td>{{ $user->userData->answers }}</td>
                                            <td>{{ $user->getInfoCompletePercent() }}</td>
                                            <td>{{ $user->userMoney->total_money }}</td>
                                            <td>{{ $user->userData->credits }}</td>
                                            <td>{{ $user->userData->coins }}</td>
                                            <td>{{ $user->created_at }}</td>
                                            <td>{{ $user->getRegisterSource() }}</td>
                                            <td><span class="label @if($user->status===0) label-danger @elseif($user->status===-1) label-default @elseif($user->status===1) label-success @endif">{{ trans_common_status($user->status) }}</span> </td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" href="{{ route('admin.user.edit',['id'=>$user->id]) }}" data-toggle="tooltip" title="基本信息"><i class="fa fa-edit"></i></a>
                                                    <a class="btn btn-default" href="{{ config('app.mobile_url').'#/share/resume?id='.$user->uuid }}" target="_blank" data-toggle="tooltip" title="查看个人名片"><i class="fa fa-book"></i></a>
                                                    <a class="btn btn-default" href="{{ route('admin.user.item.info',['item_id'=>0,'user_id'=>$user->id,'type'=>'jobs']) }}" data-toggle="tooltip" title="工作经历"><i class="fa fa-user-md"></i></a>
                                                    <a class="btn btn-default" href="{{ route('admin.user.item.info',['item_id'=>0,'user_id'=>$user->id,'type'=>'projects']) }}" data-toggle="tooltip" title="项目经历"><i class="fa fa-briefcase"></i></a>
                                                    <a class="btn btn-default" href="{{ route('admin.user.item.info',['item_id'=>0,'user_id'=>$user->id,'type'=>'edus']) }}" data-toggle="tooltip" title="教育经历"><i class="fa fa-book"></i></a>
                                                    <a class="btn btn-default" href="{{ route('admin.user.item.info',['item_id'=>0,'user_id'=>$user->id,'type'=>'trains']) }}" data-toggle="tooltip" title="培训经历"><i class="fa fa-trophy"></i></a>
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
                                    <a href="{{ route('admin.user.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="创建新用户"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.user.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="禁用选中项" onclick="confirm_submit('item_form','{{  route('admin.user.destroy') }}','确认禁用选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $users->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $users->render()) !!}
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
        set_active_menu('manage_user',"{{ route('admin.user.index') }}");
    </script>
@endsection