@extends('admin/public/layout')

@section('title')
    找顾问助手需求管理
@endsection

@section('content')
    <section class="content-header">
        <h1>
            需求列表
            <small>管理微信小程序需求</small>
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
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除选中项" onclick="confirm_submit('item_form','{{  route('admin.weapp.demand.destroy') }}','确认删除选中项？')"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-10">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.weapp.demand.index') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2 hidden-xs">
                                            <input type="text" class="form-control" name="user_id" placeholder="UID" value="{{ $filter['user_id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2 hidden-xs">
                                            <input type="text" class="form-control" name="word" placeholder="标题" value="{{ $filter['word'] or '' }}"/>
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
                                        <th>需求标题</th>
                                        <th>地点</th>
                                        <th>发布者</th>
                                        <th>手机</th>
                                        <th>身份职业</th>
                                        <th>公司</th>
                                        <th>浏览数</th>
                                        <th>咨询数</th>
                                        <th>创建时间</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($demands as $demand)
                                        <tr>
                                            <td><input type="checkbox" value="{{ $demand->id }}" name="id[]"/></td>
                                            <td>{{ $demand->user_id }}</td>
                                            <td>{{ $demand->title }}</td>
                                            <td>{{ $demand->address }}</td>
                                            <td>{{ $demand->user->name }}</td>
                                            <td>{{ $demand->user->mobile }}</td>
                                            <td>{{ $demand->user->title }}</td>
                                            <td>{{ $demand->user->company }}</td>
                                            <td>{{ $demand->views }}</td>
                                            <td>{{ $demand->getRoomCount() }}</td>
                                            <td>{{ $demand->created_at }}</td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" href="{{ route('admin.weapp.demand.detail',['id'=>$demand->id]) }}" data-toggle="tooltip" title="基本信息"><i class="fa fa-eye"></i></a>
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
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除选中项" onclick="confirm_submit('item_form','{{  route('admin.weapp.demand.destroy') }}','确认删除选中项？')"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $demands->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $demands->appends($filter)->render()) !!}
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
        set_active_menu('manage_weapp_user',"{{ route('admin.weapp.demand.index') }}");
    </script>
@endsection