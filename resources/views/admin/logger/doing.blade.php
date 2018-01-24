@extends('admin/public/layout')
@section('title')活动日志@endsection
@section('content')
    <section class="content-header">
        <h1>
            活动日志
            <small>显示所有用户活动日志</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">
                            <form name="searchForm" action="{{ route('admin.logger.doing') }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="col-xs-3">
                                    <input type="text" class="form-control" name="user_id" placeholder="UID" value="{{ $filter['user_id'] or '' }}"/>
                                </div>
                                <div class="col-xs-6">
                                    <input type="text" name="date_range" id="date_range" class="form-control" placeholder="时间范围" value="{{ $filter['date_range'] or '' }}" />
                                </div>
                                <div class="col-xs-3">
                                    <button type="submit" class="btn btn-primary">搜索</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="box-body  no-padding">
                        <form name="itemForm" id="item_form" method="POST">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <table class="table table-striped">
                                <tr>
                                    <th>用户</th>
                                    <th>行为</th>
                                    <th>备注</th>
                                    <th>对象id</th>
                                    <th>时间</th>
                                </tr>
                                @foreach($records as $record)
                                    <tr>
                                        <td> @if($record->user){{ $record->user->name }} @else 游客 @endif [uid:{{ $record->user_id }}]</td>
                                        <td>{{ $record->action }}</td>
                                        <td>{{ $record->subject }}</td>
                                        <td>{{ $record->source_id }}</td>
                                        <td>{{ timestamp_format($record->created_at) }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </form>
                    </div>
                    <div class="box-footer clearfix">
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="btn-group"></div>
                            </div>
                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $records->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $records->appends($filter)->render()) !!}
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
        set_active_menu('logger',"{{ route('admin.logger.doing') }}");
    </script>
@endsection