@extends('admin/public/layout')

@section('title')
    结算管理
@endsection

@section('content')
    <section class="content-header">
        <h1>
            结算列表
            <small>显示当前系统的所有结算,暂停和恢复只对未处理的生效</small>
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
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="立即结算" onclick="confirm_submit('item_form','{{  route('admin.finance.settlement.doitnow') }}','确认立即结算选中项？')"><i class="fa fa-plus"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="恢复" onclick="confirm_submit('item_form','{{  route('admin.finance.settlement.verify') }}','确认恢复选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="暂停选中项" onclick="confirm_submit('item_form','{{  route('admin.finance.settlement.destroy') }}','确认暂停选中项？')"><i class="fa fa-lock"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-10">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.finance.settlement.index') }}" method="GET">
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
                                        <th>结算对象</th>
                                        <th>申请时间</th>
                                        <th>结算时间</th>
                                        <th>结算金额</th>
                                        <th>结算手续费</th>
                                        <th>实际结算时间</th>
                                        <th>状态</th>
                                    </tr>
                                    @foreach($settlements as $settlement)
                                        <tr>
                                            <td><input type="checkbox" value="{{ $settlement->id }}" name="id[]"/></td>
                                            <td>{{ $settlement->id }}</td>
                                            <td>{{ $settlement->user->name }}</td>
                                            <td>{{ $settlement->user->mobile }}</td>
                                            <td>{{ $settlement->getSettlementName() }}:{{ $settlement->source_id }}</td>
                                            <td>{{ $settlement->created_at }}</td>
                                            <td>{{ $settlement->settlement_date }}</td>
                                            <td>{{ $settlement->actual_amount }}</td>
                                            <td>{{ $settlement->actual_fee }}</td>
                                            <td>{{ $settlement->actual_settlement_date }}</td>
                                            <td><span class="label @if($settlement->status>=3) label-danger @elseif($settlement->status<=1) label-default @elseif($settlement->status===2) label-success @endif">{{ trans_withdraw_status($settlement->status) }}</span> </td>
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
                                    <span class="total-num">共 {{ $settlements->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $settlements->appends($filter)->render()) !!}
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
        set_active_menu('finance',"{{ route('admin.finance.settlement.index') }}");
    </script>
@endsection