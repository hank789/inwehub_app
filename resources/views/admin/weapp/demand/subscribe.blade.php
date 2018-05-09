@extends('admin/public/layout')

@section('title')
    找顾问助手订阅管理
@endsection

@section('content')
    <section class="content-header">
        <h1>
            订阅列表
            <small>管理微信小程序订阅</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.weapp.demand.subscribe') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2 hidden-xs">
                                            <input type="text" class="form-control" name="demand_id" placeholder="需求id" value="{{ $filter['demand_id'] or '' }}"/>
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
                                        <th>需求ID</th>
                                        <th>需求标题</th>
                                        <th>订阅者ID</th>
                                        <th>订阅者邮箱</th>
                                        <th>订阅者信息</th>
                                        <th>订阅时间</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($demands as $demand)
                                        <tr>
                                            <td><input type="checkbox" value="{{ $demand->id }}" name="id[]"/></td>
                                            <td>{{ $demand->demand_id }}</td>
                                            <td>{{ $demand->demand->title }}</td>
                                            <td>{{ $demand->user_id }}</td>
                                            <td>{{ $demand->user->email }}</td>
                                            <td>{{ $demand->formatSubscribes() }}</td>
                                            <td>{{ $demand->updated_at }}</td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" href="{{ route('admin.weapp.demand.detail',['id'=>$demand->demand_id]) }}" data-toggle="tooltip" title="基本信息"><i class="fa fa-eye"></i></a>
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
                            <div class="col-sm-12">
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