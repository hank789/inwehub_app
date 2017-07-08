@extends('theme::layout.public')

@section('seo_title')编辑回答 - {{ Setting()->get('website_name') }}>@endsection

@section('css')
    <link href="https://cdn.quilljs.com/1.0.0/quill.snow.css" rel="stylesheet">
    <style>
        .quill-editor img {
            max-width: 100%;
        }
    </style>
@endsection

@section('content')

    <div class="row mt-10">
        <ol class="breadcrumb">
            <li><a href="{{ route('website.ask') }}">问答</a></li>
            <li><a href="{{ route('ask.question.detail',['id'=>$answer->question_id]) }}">{{ $answer->question->title }}</a></li>
            <li class="active">编辑回答</li>
        </ol>
        <form id="answer_form" method="POST" role="form" action="{{ route('ask.answer.update',['id'=>$answer->id]) }}">
            <input type="hidden" id="editor_token" name="_token" value="{{ csrf_token() }}">

            <div class="form-group  @if($errors->has('content')) has-error @endif">
                <div id="answer_editor"></div>
                @if($errors->has('content')) <p class="help-block">{{ $errors->first('content') }}</p> @endif
            </div>

            <div class="row mt-20">
                <div class="col-xs-12 col-md-11">
                    <ul class="list-inline">
                        @if( Setting()->get('code_create_answer') )
                            <li class="pull-right">
                                <div class="form-group @if ($errors->first('captcha')) has-error @endif">
                                    <input type="text" class="form-control" name="captcha" required="" placeholder="验证码" />
                                    @if ($errors->first('captcha'))
                                        <span class="help-block">{{ $errors->first('captcha') }}</span>
                                    @endif
                                    <div class="mt-10"><a href="javascript:void(0);" id="reloadCaptcha"><img src="{{ captcha_src()}}"></a></div>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
                <div class="col-xs-12 col-md-1">
                    <input type="hidden" id="answer_editor_content"  name="content" value="{{ $answer->content }}"  />
                    <button type="submit" class="btn btn-primary pull-right editor-submit" onclick="setContent()">保存修改</button>
                </div>
            </div>
        </form>
    </div>

@endsection
@section('script')
    <script src="//cdn.quilljs.com/1.0.0/quill.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        /*回答编辑器初始化*/
        var editor = new Quill('#answer_editor', {
            modules: { toolbar: [
                [{ header: [1, 2, false] }],
                ['bold', 'italic', 'underline']
            ] },
            theme: 'snow'
        });
        editor.setContents({!! $answer->content !!});

        function setContent(){
            $("#answer_editor_content").val(JSON.stringify(editor.getContents()));
        }
    </script>
@endsection