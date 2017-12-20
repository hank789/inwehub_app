@extends('admin/public/layout')

@section('title')
    私信群发
@endsection

@section('content')
    <section class="content-header">
        <h1>
            群发私信
            <small>使用客服小哈群发私信</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-default">
                    <form role="form" name="userForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.im.customer.group') }}">
                        <input name="_method" type="hidden" value="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group @if ($errors->has('description')) has-error @endif">
                                <label for="name">内容</label>
                                <textarea name="message" id="message" class="form-control " placeholder="私信内容">{{ old('message',$message) }}</textarea>
                                @if ($errors->has('message')) <p class="help-block">{{ $errors->first('message') }}</p> @endif
                            </div>

                        </div>
                        <div class="box-footer">
                            <a class="btn btn-warning" href="#" onclick="showTestSendModal(this)" data-toggle="tooltip" title="群发测试">群发测试</a>
                            <button type="submit" class="btn btn-success">立即发送</button>
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
                    <h4 class="modal-title" id="exampleModalLabel">测试群发</h4>
                </div>
                <div class="modal-body">
                    <form id="test_push_notice_from" method="POST" action="{{ route('admin.im.customer.groupTest') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="test_message" name="test_message" />

                        <div class="form-group">
                            <label for="test_user_id" class="control-label">发送给用户(ID):</label>
                            <input type="text" id="test_user_id" name="test_user_id" class="form-control "  placeholder="输入测试用户ID" value="">
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
@endsection
@section('script')
    <script src="{{ asset('/static/js/autosize.min.js')}}"></script>

    <script type="text/javascript">
        set_active_menu('im',"{{ route('admin.im.customer.group') }}");

        $(function(){
            autosize($('textarea'));
            $("#test_push_notice_modal_submit").click(function(){
                var test_user_id = $("#test_user_id").val();

                if( test_user_id ){
                    $("#test_push_notice_from").submit();
                }else{
                    alert("您没有输入任何内容");
                }
            });
        });
        function showTestSendModal(obj){
            $('#test_message').val($('#message').val());
            $('#test_push_notice_modal').modal('show');
        }
    </script>
@endsection