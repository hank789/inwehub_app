@extends('theme::layout.public')

@section('seo_title')创建活动报名 - {{ Setting()->get('website_name') }}@endsection

@section('css')
    <link href="{{ asset('/static/js/summernote/summernote.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection

@section('content')

    <div class="row mt-10">
        <ol class="breadcrumb">
            <li><a href="{{ route('website.blog') }}">活动</a></li>
            <li class="active">活动报名</li>
        </ol>
        <form id="article_form" method="POST" role="form" enctype="multipart/form-data" action="{{ route('blog.article.store') }}">
            <input type="hidden" id="editor_token" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" id="tags" name="tags" value="" />

            <div class="form-group @if($errors->has('title')) has-error @endif ">
                <label for="title">活动标题:</label>
                <input id="title" type="text" name="title"  class="form-control input-lg" placeholder="" value="{{ old('title','') }}" />
                @if($errors->has('title')) <p class="help-block">{{ $errors->first('title') }}</p> @endif
            </div>
            <div class="form-group @if($errors->has('logo')) has-error @endif">
                <label>活动封面</label>
                <input type="file" name="logo"/>
                @if($errors->has('logo')) <p class="help-block">{{ $errors->first('logo') }}</p> @else <p class="help-block">建议尺寸200*120</p> @endif
            </div>
            <div class="form-group  @if($errors->has('content')) has-error @endif">
                <label for="article_editor">活动正文：</label>
                <div id="article_editor">{!! old('content','') !!}</div>
                @if($errors->has('content')) <p class="help-block">{{ $errors->first('content') }}</p> @endif
            </div>

            <div class="form-group">
                <label for="editor">活动导读（可为空）：</label>
                <textarea name="summary" class="form-control" placeholder="（可为空）">{{ old('summary','') }}</textarea>
            </div>

            <div class="row">
                <div class="col-xs-3">
                    <label for="editor">分类：</label>
                    <select name="category_id" id="category_id" class="form-control">
                        <option value="0">请选择分类</option>
                        @include('admin.category.option',['type'=>'articles','select_id'=>old('category_id',0),'root'=>false])
                    </select>
                </div>
                <div class="col-xs-3">
                    <label for="editor">截止时间(Ex:2017-01-02 13:00)：</label>
                    <input type="text" name="deadline" class="form-control datepicker" placeholder="截止日期，留空永久有效" value="{{ old('deadline','') }}" />
                </div>
                <div class="col-xs-3">
                    <label for="editor">首页排序，留空则不推荐到首页：</label>
                    <input type="text" name="recommend_home_sort" class="form-control datepicker" placeholder="推荐到首页的排序(值越小，越靠前)，如不推荐到首页，留空" value="{{ old('recommend_home_sort','')}}" />
                </div>
                <div class="col-xs-3">
                    <label for="editor">首页封面图地址：</label>
                    <input type="text" name="recommend_home_img" class="form-control datepicker" placeholder="推荐到首页用的图片，如不推荐到首页，留空" value="{{ old('recommend_home_img','')  }}" />
                </div>
            </div>

            <div class="row mt-20">
                <div class="col-xs-12 col-md-11">
                    <ul class="list-inline">
                        @if( Setting()->get('code_create_article') )
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
                    <input type="hidden" id="article_editor_content"  name="content" value="{{ old('content','') }}"  />
                    <button type="submit" class="btn btn-primary pull-right editor-submit">发布文章</button>
                </div>
            </div>
        </form>

    </div>

@endsection
@section('script')
    <script src="{{ asset('/static/js/summernote/summernote.min.js') }}"></script>
    <script src="{{ asset('/static/js/summernote/lang/summernote-zh-CN.min.js') }}"></script>
    <script src="{{ asset('/static/js/select2/js/select2.min.js')}}"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#article_editor').summernote({
                lang: 'zh-CN',
                height: 350,
                placeholder:'活动内容',
                callbacks: {
                    onChange:function (contents, $editable) {
                        var code = $(this).summernote("code");
                        $("#article_editor_content").val(code);
                    },
                    onImageUpload: function(files) {
                        upload_editor_image(files[0],'article_editor');
                    }
                }
            });

        });
    </script>
@endsection