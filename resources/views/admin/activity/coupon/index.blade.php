@extends('admin/public/layout')

@section('title')
    红包管理
@endsection

@section('content')
    <section class="content-header">
        <h1>
            红包列表
            <small>显示当前系统发放的红包记录</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">

                            <div class="col-xs-10">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.activity.coupon') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2 hidden-xs">
                                            <input type="text" class="form-control" name="user_id" placeholder="UID" value="{{ $filter['user_id'] or '' }}"/>
                                        </div>

                                        <div class="col-xs-2">
                                            <select class="form-control" name="status">
                                                <option value="-9">不选择</option>
                                                @foreach(trans_coupon_status('all') as $key => $status)
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
                                        <th>红包类型</th>
                                        <th>红包金额</th>
                                        <th>领取时间</th>
                                        <th>使用时间</th>
                                        <th>过期时间</th>
                                        <th>使用对象</th>
                                        <th>状态</th>
                                    </tr>
                                    @foreach($coupons as $coupon)
                                        <tr>
                                            <td>{{ $coupon->id }}</td>
                                            <td>{{ $coupon->user->name }}</td>
                                            <td>{{ $coupon->user->mobile }}</td>
                                            <td>{{ $coupon->getCouponTypeName() }}</td>
                                            <td>{{ $coupon->coupon_value }}</td>
                                            <td>{{ $coupon->created_at }}</td>
                                            <td>{{ $coupon->used_at }}</td>
                                            <td>{{ $coupon->expire_at }}</td>
                                            <td>@if($coupon->used_object_id) <a href="{{ $coupon->getObjectTypeLink() }}" target="_blank">链接</a> @endif</td>
                                            <td><span class="label @if($coupon->coupon_status==3) label-danger @elseif($coupon->coupon_status<=1) label-default @elseif($coupon->coupon_status===2) label-success @endif">{{ trans_coupon_status($coupon->coupon_status) }}</span> </td>
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
                                    <span class="total-num">共 {{ $coupons->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $coupons->render()) !!}
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
        set_active_menu('activity',"{{ route('admin.activity.coupon') }}");
    </script>
@endsection