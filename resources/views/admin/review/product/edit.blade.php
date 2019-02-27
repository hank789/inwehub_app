@extends('admin/public/layout')

@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/css/fileinput/fileinput.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/css/fileinput/theme.css')}}" rel="stylesheet">
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
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab_base" data-toggle="tab" aria-expanded="false">产品编辑</a></li>
                        <li><a href="#tab_news" data-toggle="tab" aria-expanded="true">产品亮点</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_base">
                            <div class="box box-default">
                    <form role="form" name="tagForm" id="tag_form" method="POST" enctype="multipart/form-data" action="{{ route('admin.review.product.update',['id'=>$tag->id]) }}">
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
                                <input type="file" accept="image/*" name="logo" />
                                @if($tag->tag->logo)
                                <div style="margin-top: 10px;">
                                    <img style="width: 150px;height: 150px;" src="{{ $tag->tag->logo }}" />
                                </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label>产品介绍图</label>
                                <input onchange="uploadAndPreviewImg(1,'cover_pic','ccc')" type="file" id="cover_pic" accept="image/*" name="cover_pic" />
                                <fieldset style="width:500px;">
                                    <div style="position: relative;" id="ccc">
                                    </div>
                                </fieldset>
                                @if($tag->tag->getCoverPic())
                                    <div style="margin-top: 10px;">
                                        <img style="width: 150px;height: 150px;" src="{{ $tag->tag->getCoverPic() }}" />
                                    </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label>分类</label>
                                <select id="category_id" name="category_id[]" class="form-control" multiple="multiple" >
                                    @foreach(load_categories(['enterprise_review','product_album'],false,true) as $category)
                                        <option value="{{ $category->id }}" @if($category->id == $tag->category_id) selected @endif>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="form-group @if ($errors->has('summary')) has-error @endif">
                                <label for="name">简介(供前台展示)</label>
                                <textarea name="summary" class="form-control" placeholder="简介" style="height: 280px;">{{ old('summary',$tag->tag->summary) }}</textarea>
                                @if ($errors->has('summary')) <p class="help-block">{{ $errors->first('summary') }}</p> @endif
                            </div>

                            <div class="form-group @if ($errors->has('description')) has-error @endif">
                                <label for="name">关键词(供搜索用,多个以逗号隔开)</label>
                                <textarea name="description" class="form-control" placeholder="关键词" style="height: 80px;">{{ old('description',$tag->tag->getKeywords()) }}</textarea>
                                @if ($errors->has('description')) <p class="help-block">{{ $errors->first('description') }}</p> @endif
                            </div>

                            <div class="form-group @if ($errors->first('status')) has-error @endif">
                                <label>专业版</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="is_pro" value="0" @if($tag->is_pro==0) checked @endif /> 否
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="is_pro" value="1" @if($tag->is_pro==1) checked @endif /> 是
                                    </label>
                                </div>
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
                            <button type="submit" class="btn btn-primary editor-submit" >保存</button>
                            <button type="reset" class="btn btn-success">重置</button>
                        </div>
                    </form>
                </div>
                        </div>
                        <div class="tab-pane" id="tab_news">
                                <input name="_method" type="hidden" value="POST">
                                <input type="hidden" name="_token" id="editor_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="deletedImages" id="deletedImages" value="">

                                <div class="box-body">
                                    <div class="file-loading">
                                        <label>Preview File Icon</label>
                                        <input id="introduce_pic" name="introduce_pic[]" type="file" multiple accept="image/*">
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
    <script src="{{ asset('/static/js/select2/js/select2.min.js')}}"></script>
    <script src="{{ asset('/static/js/fileinput/piexif.min.js')}}"></script>
    <script src="{{ asset('/static/js/fileinput/purify.min.js')}}"></script>
    <script src="{{ asset('/static/js/sortable.min.js')}}"></script>
    <script src="{{ asset('/static/js/fileinput/fileinput.min.js')}}"></script>
    <script src="{{ asset('/static/js/fileinput/theme.js')}}"></script>

    <script type="text/javascript">
        $(function() {
            set_active_menu('manage_review', "{{ route('admin.review.product.index') }}");
            $('#category_id').select2();
            /*
            $(".file").on('fileselect', function(event, n, l) {
            alert('File Selected. Name: ' + l + ', Num: ' + n);
            });
            */
            $("#introduce_pic").fileinput({
                theme: 'fas',
                uploadUrl: "{{route('admin.review.product.updateIntroducePic',['id'=>$tag->tag_id])}}",
                showUpload: true,
                uploadAsync: false,
                maxFileCount: 5,
                showCaption: false,
                allowedFileExtensions: ['jpg', 'png','jpeg', 'gif'],
                overwriteInitial: false,
                initialPreviewAsData: true,
                initialPreview: {!! $initialPreview !!},
                initialPreviewConfig: {!! $initialPreviewConfig !!}
            });
            $('#introduce_pic').on('filesorted', function(event, params) {
                console.log('File sorted previewId:'+ params.previewId + ';oldIndex:' +params.oldIndex + ';newIndex'+params.newIndex);
                console.log(params.stack);
                $.ajax({
                    type: "post",
                    data: {newList: params.stack},
                    url:"{{route('admin.review.product.sortIntroducePic',['id'=>$tag->tag_id])}}",
                    success: function(data){
                        console.log(data);
                    },
                    error: function(data){
                        console.log(data);
                    }
                });
            });
        })
    </script>
@endsection
