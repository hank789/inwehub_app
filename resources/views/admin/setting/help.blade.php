@extends('admin/public/layout')
@section('title')常见问题设置@endsection
@section('css')
    <link href="{{ asset('/static/js/summernote/summernote.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>
            常见问题设置
            <small>常见问题设置</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form role="form" name="addForm" id="register_form" method="POST" action="{{ route('admin.setting.help') }}">
                        <input type="hidden" name="_token" id="editor_token" value="{{ csrf_token() }}">
                        <div class="box-body">

                            <div class="form-group">
                                <label for="register_editor">常见问题</label>
                                <div id="register_editor">{!! old('app_help',Setting()->get('app_help')) !!}</div>
                            </div>

                        </div>
                        <div class="box-footer">
                            <input type="hidden" id="register_editor_content"  name="app_help" value="{{ old('app_help',Setting()->get('app_help')) }}"  />
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
            set_active_menu('global',"{{ route('admin.setting.help') }}");
            $('#register_editor').summernote({
                lang: 'zh-CN',
                height: 600,
                placeholder:'常见问题',
                callbacks: {
                    onChange:function (contents, $editable) {
                        var code = $(this).summernote("code");
                        $("#register_editor_content").val(code);
                    },
                    onImageUpload: function(files) {
                        upload_editor_image(files[0],'register_editor');
                    }
                }
            });

        });
    </script>
@endsection