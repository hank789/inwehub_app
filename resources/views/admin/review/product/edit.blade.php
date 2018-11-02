@extends('admin/public/layout')

@section('css')
    <link href="{{ asset('/static/js/summernote/summernote.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection

@section('title')
    编辑产品
@endsection

@section('content')
    <section class="content-header">
        <h1>
            编辑产品
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-default">
                    <form role="form" name="tagForm" id="tag_form" method="POST" enctype="multipart/form-data" action="{{ route('admin.review.product.update',['id'=>$tag->tag_id,'cid'=>$tag->category_id]) }}">
                        <input name="_method" type="hidden" value="PUT">
                        <input type="hidden" name="_token" id="editor_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group @if ($errors->has('name')) has-error @endif">
                                <label for="name">产品名称</label>
                                <input type="text" name="name" class="form-control " placeholder="标签名称" value="{{ old('name',$tag->tag->name) }}">
                                @if ($errors->has('name')) <p class="help-block">{{ $errors->first('name') }}</p> @endif
                            </div>

                            <div class="form-group">
                                <label>产品图标</label>
                                <input type="file" name="logo" />
                                @if($tag->tag->logo)
                                <div style="margin-top: 10px;">
                                    <img src="{{ $tag->tag->logo }}" />
                                </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label>分类</label>
                                <select id="category_id" name="category_id[]" class="form-control" multiple="multiple" >
                                    <option value="0">选择分类</option>
                                    @foreach(load_categories('enterprise_review',false,true) as $category)
                                        <option value="{{ $category->id }}" @if($category->id == $tag->category_id) selected @endif>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="form-group @if ($errors->has('summary')) has-error @endif">
                                <label for="name">简介(供前台展示)</label>
                                <textarea name="summary" class="form-control" placeholder="简介" style="height: 80px;">{{ old('summary',$tag->tag->summary) }}</textarea>
                                @if ($errors->has('summary')) <p class="help-block">{{ $errors->first('summary') }}</p> @endif
                            </div>

                            <div class="form-group @if ($errors->has('description')) has-error @endif">
                                <label for="name">详细介绍(可不填)</label>
                                <div id="tag_editor">{!! old('description',$tag->tag->description) !!}</div>
                                @if ($errors->has('description')) <p class="help-block">{{ $errors->first('description') }}</p> @endif
                            </div>

                            <div class="form-group @if ($errors->first('status')) has-error @endif">
                                <label>审核状态</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="status" value="0" @if($tag->status==0) checked @endif /> 待审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="status" value="1" @if($tag->status==1) checked @endif /> 已审核
                                    </label>
                                </div>
                            </div>

                        </div>
                        <div class="box-footer">
                            <input type="hidden" id="tag_editor_content"  name="description" value="{{ old('description',$tag->tag->description) }}" />
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
    <script src="{{ asset('/static/js/select2/js/select2.min.js')}}"></script>
    <script type="text/javascript">
        $(function(){
            set_active_menu('manage_review',"{{ route('admin.review.product.index') }}");
            $('#category_id').select2();
            $('#tag_editor').summernote({
                lang: 'zh-CN',
                height: 300,
                placeholder:'完善话题详情',
                toolbar: [ {!! config('inwehub.summernote.blog') !!} ],
                callbacks: {
                    onChange:function (contents, $editable) {
                        var code = $(this).summernote("code");
                        $("#tag_editor_content").val(code);
                    },
                    onImageUpload: function(files) {
                        upload_editor_image(files[0],'tag_editor');
                    }
                }
            });

        });
    </script>
@endsection
