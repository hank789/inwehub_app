@extends('admin/public/layout')

@section('title')
    点评小程序数据统计
@endsection

@section('content')
    <section class="content-header">
        <h1>
            点评小程序数据统计
            <small>统计页面访问记录</small>
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
                                    <form name="searchForm" action="{{ route('admin.data.weappDianpingViews') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2 hidden-xs">
                                            <input type="text" class="form-control" name="user_id" placeholder="UID" value="{{ $filter['user_id'] or '' }}"/>
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
                                        <th>用户</th>
                                        <th>页面</th>
                                        <th>对象</th>
                                        <th>场景值</th>
                                        <th>分享者oauth_id</th>
                                        <th>访问开始时间</th>
                                        <th>访问结束时间</th>
                                        <th>停留时间(秒)</th>
                                    </tr>
                                    @foreach($data as $item)
                                        <tr>
                                            <td>{{ $item->getUserName().'['.$item->user_oauth_id.']' }}</td>
                                            <td>{{ $item->page.'['.$item->getPageName().']' }}</td>
                                            <td>{{ $item->getPageObject() }}</td>
                                            <td>{{ $item->scene }}</td>
                                            <td>{{ $item->from_user_id }}</td>
                                            <td>{{ date('Y-m-d H:i:s',$item->start_time/1000) }}</td>
                                            <td>{{ date('Y-m-d H:i:s',$item->end_time/1000) }}</td>
                                            <td>{{ $item->stay_time/1000 }}</td>
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
                                    <span class="total-num">共 {{ $data->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $data->appends($filter)->render()) !!}
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
        set_active_menu('manage_data',"{{ route('admin.data.weappDianpingViews') }}");
    </script>
@endsection