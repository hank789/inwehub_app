@extends('admin.public.layout')
@section('title')客服聊天管理@endsection
@section('content')
    <section class="content-header">
        <h1>
            客服聊天管理
            <small>管理客服的聊天信息</small>
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
                                    <form name="searchForm" action="{{ route('admin.im.customer.index') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="user_id" placeholder="用户id" value="{{ $filter['user_id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-3">
                                            <div>
                                                <label><input type="checkbox" name="is_unread" value="1" @if ( $filter['is_unread']??0) checked @endif >未读</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                            </div>
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
                                        <th>最新消息</th>
                                        <th>发布时间</th>
                                        <th>是否已读</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($messages as $message)
                                        <tr>
                                            <td>{{ $message->contact->name }}</td>
                                            <td>{{ $message->last_message->data['text'] }}</td>
                                            <td>{{ timestamp_format($message->last_message->created_at) }}</td>
                                            <td>{{ $message->last_message->read_at?:'未读' }}</td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a target="_blank" class="btn btn-default" href="{{ route('auth.message.show',['id'=>$message->contact->id]) }}" data-toggle="tooltip" title="查看对话"><i class="fa fa-comment-o"></i></a>
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

                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $messages->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $messages->render()) !!}
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
        set_active_menu('im',"{{ route('admin.im.customer.index') }}");
    </script>
@endsection