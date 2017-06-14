@extends('admin/public/layout')

@section('title')
    提现管理
@endsection

@section('content')
    <section class="content-header">
        <h1>
            提现列表
            <small>显示当前系统的所有提现</small>
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
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="处理提现" onclick="confirm_submit('item_form','{{  route('admin.finance.withdraw.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-9">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.finance.withdraw.index') }}" method="GET">
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
                                        <th><input type="checkbox" class="checkbox-toggle"/></th>
                                        <th>ID</th>
                                        <th>姓名</th>
                                        <th>手机</th>
                                        <th>金额</th>
                                        <th>申请日期</th>
                                        <th>入款账户</th>
                                        <th>流水号</th>
                                        <th>信息</th>
                                        <th>状态</th>
                                    </tr>
                                    @foreach($withdraws as $withdraw)
                                        <tr>
                                            <td><input type="checkbox" value="{{ $withdraw->id }}" name="id[]"/></td>
                                            <td>{{ $withdraw->id }}</td>
                                            <td>{{ $withdraw->user->name }}</td>
                                            <td>{{ $withdraw->user->mobile }}</td>
                                            <td>{{ $withdraw->amount }}</td>
                                            <td>{{ $withdraw->created_at }}</td>
                                            <td>{{ $withdraw->getAccount() }}</td>
                                            <td>{{ $withdraw->order_no }}</td>
                                            <td>{{ $withdraw->response_msg }}</td>
                                            <td><span class="label @if($withdraw->status===3) label-danger @elseif($withdraw->status<=1) label-default @elseif($withdraw->status===2) label-success @endif">{{ trans_withdraw_status($withdraw->status) }}</span> </td>
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
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="处理提现" onclick="confirm_submit('item_form','{{  route('admin.finance.withdraw.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $withdraws->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $withdraws->render()) !!}
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
        set_active_menu('finance',"{{ route('admin.finance.withdraw.index') }}");
    </script>
@endsection