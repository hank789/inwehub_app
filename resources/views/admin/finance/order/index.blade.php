@extends('admin/public/layout')

@section('title')
    支付订单管理
@endsection

@section('content')
    <section class="content-header">
        <h1>
            支付订单列表
            <small>显示当前系统的所有支付订单</small>
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
                                    <form name="searchForm" action="{{ route('admin.finance.order.index') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2 hidden-xs">
                                            <input type="text" class="form-control" name="user_id" placeholder="UID" value="{{ $filter['user_id'] or '' }}"/>
                                        </div>

                                        <div class="col-xs-2">
                                            <select class="form-control" name="status">
                                                <option value="-9">不选择</option>
                                                @foreach(trans_withdraw_status('all') as $key => $status)
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
                                        <th>ID</th>
                                        <th>姓名</th>
                                        <th>手机</th>
                                        <th>订单title</th>
                                        <th>创建时间</th>
                                        <th>完成时间</th>
                                        <th>支付金额</th>
                                        <th>支付方式</th>
                                        <th>状态</th>
                                    </tr>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>{{ $order->id }}</td>
                                            <td>{{ $order->user->name }}</td>
                                            <td>{{ $order->user->mobile }}</td>
                                            <td>{{ $order->subject }}</td>
                                            <td>{{ $order->created_at }}</td>
                                            <td>{{ $order->finish_time }}</td>
                                            <td>{{ $order->actual_amount }}</td>
                                            <td>{{ $order->getPayChannelName() }}</td>
                                            <td><span class="label @if($order->status>=3) label-danger @elseif($order->status<=1) label-default @elseif($order->status===2) label-success @endif">{{ trans_withdraw_status($order->status) }}</span> </td>
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
                                    <span class="total-num">共 {{ $orders->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $orders->appends($filter)->render()) !!}
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
        set_active_menu('finance',"{{ route('admin.finance.order.index') }}");
    </script>
@endsection