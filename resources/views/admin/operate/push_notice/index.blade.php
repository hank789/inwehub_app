@extends('admin/public/layout')

@section('title')推送管理@endsection

@section('content')
    <section class="content-header">
        <h1>推送管理</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <form role="form" name="listForm" method="post" action="{{ route('admin.operate.pushNotice.destroy',['id'=>0]) }}">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-header">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.operate.pushNotice.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="创建推送"><i class="fa fa-plus"></i></a>
                                        <button type="submit" class="btn btn-default btn-sm" data-toggle="tooltip" title="删除推送"><i class="fa fa-trash-o"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-body  no-padding">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <tr>
                                        <th><input type="checkbox" class="checkbox-toggle"/></th>
                                        <th>ID</th>
                                        <th>标题</th>
                                        <th>地址</th>
                                        <th>类型</th>
                                        <th>状态</th>
                                        <th>更新时间</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($notices as $notice)
                                        <tr>
                                            <td><input type="checkbox" value="{{ $notice->id }}" name="ids[]"/></td>
                                            <td>{{ $notice->id }}</td>
                                            <td>{{ $notice->title }}</td>
                                            <td>{{ $notice->url }}</td>
                                            <td>{{ trans_push_notice_notification_type($notice->notification_type) }}</td>
                                            <td><span class="label @if($notice->status===0) label-warning @elseif ($notice->status===1) label-default
                                                  @else label-success @endif">{{ trans_push_notice_status($notice->status) }}</span> </td>
                                            <td>{{ $notice->updated_at }}</td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    @if ($notice->status != 2)
                                                        <a class="btn btn-default" href="{{ route('admin.operate.pushNotice.edit',['id'=>$notice->id]) }}" data-toggle="tooltip" title="编辑信息"><i class="fa fa-edit"></i></a>
                                                        <a class="btn btn-warning" href="#" onclick="showTestPushModal(this)" data-pid="{{ $notice->id }}" data-ptitle="{{ $notice->title }}" data-toggle="tooltip" title="推送测试">推送测试</a>
                                                        <a class="btn btn-danger" href="#" onclick="showPushModal(this)" data-pid="{{ $notice->id }}" data-ptitle="{{ $notice->title }}" data-toggle="tooltip" title="发送推送">发送推送</a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                        <div class="box-footer clearfix">
                            {!! str_replace('/?', '?', $notices->render()) !!}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="test_push_notice_modal" tabindex="-1"  role="dialog" aria-labelledby="change_category_modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">测试推送</h4><span id="test_push_title"></span>
                </div>
                <div class="modal-body">
                    <form id="test_push_notice_from" method="POST" action="{{ route('admin.operate.pushNotice.test') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="test_push_id" name="test_push_id" />
                        <div class="form-group">
                            <label for="push_user_id" class="control-label">推送给用户(ID):</label>
                            <input type="text" id="test_push_user_id" name="test_push_user_id" class="form-control "  placeholder="输入测试用户ID" value="">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="test_push_notice_modal_submit">确认</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="true_push_notice_modal" tabindex="-1"  role="dialog" aria-labelledby="change_category_modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">正式发送推送</h4>
                </div>
                <div class="modal-body">
                    <form id="push_notice_from" method="POST" action="{{ route('admin.operate.pushNotice.verify') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="push_id" name="push_id" />
                        <div class="form-group">
                            <label class="control-label">标题</label>
                            <span id="push_title"></span>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="true_push_notice_modal_submit">确认</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script type="text/javascript">
        set_active_menu('operations',"{{ route('admin.operate.pushNotice.index') }}");

        $(function(){

            $("#test_push_notice_modal_submit").click(function(){
                var test_push_user_id = $("#test_push_user_id").val();

                if( test_push_user_id ){
                    $("#test_push_notice_from").submit();
                }else{
                    alert("您没有输入任何内容");
                }
            });

            $("#true_push_notice_modal_submit").click(function(){
                var test_push_user_id = $("#test_push_user_id").val();

                $("#push_notice_from").submit();

            });

        });

        function showTestPushModal(obj){
            $('#test_push_id').val($(obj).data('pid'));
            $('#test_push_title').html($(obj).data('ptitle'));
            $('#test_push_notice_modal').modal('show');
        }

        function showPushModal(obj){
            $('#push_id').val($(obj).data('pid'));
            $('#push_title').html($(obj).data('ptitle'));
            $('#true_push_notice_modal').modal('show');
        }
    </script>
@endsection