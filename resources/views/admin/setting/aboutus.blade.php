@extends('admin/public/layout')
@section('title')关于我们设置@endsection
@section('css')
    <link href="{{ asset('/static/js/summernote/summernote.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>
            关于我们设置
            <small>关于我们设置</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form role="form" name="addForm" id="register_form" method="POST" action="{{ route('admin.setting.aboutus') }}">
                        <input type="hidden" name="_token" id="editor_token" value="{{ csrf_token() }}">
                        <div class="box-body">

                            <div class="form-group">
                                <label for="register_editor">关于我们</label><span class="text-muted">(<a target="_blank" href="{{ route('website.service.about') }}">点击预览</a>)</span>
                                <div id="register_editor">{!! old('about_us',Setting()->get('about_us')) !!}</div>
                            </div>

                        </div>
                        <div class="box-footer">
                            <input type="hidden" id="register_editor_content"  name="about_us" value="{{ old('about_us',Setting()->get('about_us')) }}"  />
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
            set_active_menu('global',"{{ route('admin.setting.aboutus') }}");
            $('#register_editor').summernote({
                lang: 'zh-CN',
                height: 600,
                placeholder:'关于我们内容',
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