@extends('admin/public/layout')
@section('title')邀请注册规则设置@endsection
@section('css')
    <link href="{{ asset('/static/js/summernote/summernote.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>
            邀请注册规则设置
            <small>邀请注册规则设置</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form role="form" name="addForm" id="register_form" method="POST" action="{{ route('admin.setting.inviterules') }}">
                        <input type="hidden" name="_token" id="editor_token" value="{{ csrf_token() }}">
                        <div class="box-body">

                            <div class="form-group">
                                <label for="setting_invite_rules_editor">邀请注册规则</label>
                                <div id="setting_invite_rules_editor">{!! old('setting_invite_rules',Setting()->get('setting_invite_rules')) !!}</div>
                            </div>

                        </div>
                        <div class="box-footer">
                            <input type="hidden" id="setting_invite_rules_editor_content"  name="setting_invite_rules" value="{{ old('setting_invite_rules',Setting()->get('setting_invite_rules')) }}"  />
                            <button type="submit" class="btn btn-primary editor-submit" >保存</button>
                            <button type="reset" class="btn btn-success">重置</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </section>
@endsection
@section('script')
    <script src="{{ asset('/static/js/summernote/summernote.min.js') }}"></script>
    <script src="{{ asset('/static/js/summernote/lang/summernote-zh-CN.min.js') }}"></script>
    <script type="text/javascript">
        $(function(){
            set_active_menu('global',"{{ route('admin.setting.inviterules') }}");
            $('#setting_invite_rules_editor').summernote({
                lang: 'zh-CN',
                height: 600,
                placeholder:'邀请注册内容',
                callbacks: {
                    onChange:function (contents, $editable) {
                        var code = $(this).summernote("code");
                        $("#setting_invite_rules_editor_content").val(code);
                    },
                    onImageUpload: function(files) {
                        upload_editor_image(files[0],'setting_invite_rules_editor');
                    }
                }
            });

        });
    </script>
@endsection