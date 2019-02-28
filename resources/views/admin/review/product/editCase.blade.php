@extends('admin/public/layout')

@section('title')
    修改产品案例
@endsection

@section('content')
    <section class="content-header">
        <h1>
            修改产品案例
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-default">
                    <form role="form" name="tagForm" id="tag_form" method="POST" enctype="multipart/form-data" action="{{ route('admin.review.product.updateCase',['id'=>$case->id]) }}">
                        <input type="hidden" name="_token" id="editor_token" value="{{ csrf_token() }}">

                        <div class="box-body">
                            <div class="form-group">
                                <label>产品名称</label>
                                <span>{{ $tag->name }}</span>
                            </div>

                            <div class="form-group @if ($errors->has('title')) has-error @endif">
                                <label for="title">案例标题</label>
                                <input type="text" name="title" class="form-control " placeholder="案例标题" value="{{ old('title',$case->content['title']) }}">
                                @if ($errors->has('title')) <p class="help-block">{{ $errors->first('title') }}</p> @endif
                            </div>

                            <div class="form-group">
                                <label>封面图</label>
                                <input onchange="uploadAndPreviewImg(1,'cover_pic','ccc')" type="file" id="cover_pic" accept="image/*" name="cover_pic" />
                                <fieldset style="width:500px;">
                                    <div style="position: relative;" id="ccc">
                                    </div>
                                </fieldset>
                                <div style="margin-top: 10px;">
                                    <img style="width: 150px;height: 150px;" src="{{ $case->content['cover_pic'] }}" />
                                </div>
                            </div>

                            <div class="form-group @if ($errors->has('desc')) has-error @endif">
                                <label for="desc">案例简介(供前台展示)</label>
                                <textarea name="desc" class="form-control" placeholder="案例简介（供前台显示）" style="height: 80px;">{{ old('desc',$case->content['desc']) }}</textarea>
                                @if ($errors->has('desc')) <p class="help-block">{{ $errors->first('desc') }}</p> @endif
                            </div>

                            <div class="form-group @if ($errors->first('type')) has-error @endif">
                                <label>案例类型</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="type" value="link" @if ($case->content['type'] == 'link') checked @endif/> 公众号链接
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="type" value="image" @if ($case->content['type'] == 'image') checked @endif /> 图片
                                    </label>
                                    <label>
                                        <input type="radio" name="type" value="pdf" @if ($case->content['type'] == 'pdf') checked @endif /> PDF文档
                                    </label>
                                    <label>
                                        <input type="radio" name="type" value="video" @if ($case->content['type'] == 'video') checked @endif /> 视频链接
                                    </label>
                                </div>
                            </div>

                            <div class="form-group @if ($errors->has('link_url')) has-error @endif">
                                <label for="link_url">案例地址</label>
                                <div id="upload_file" style="display: {{ in_array($case->content['type'],['pdf','image'])?'block':'none' }};">
                                    <input name="file" type="file">
                                </div>
                                <input id="link_url" type="text" name="link_url" class="form-control" placeholder="案例地址" value="{{ old('link_url',$case->content['link_url']) }}">
                                @if ($errors->has('link_url')) <p class="help-block">{{ $errors->first('link_url') }}</p> @endif
                            </div>

                            <div class="form-group @if ($errors->has('sort')) has-error @endif">
                                <label for="title">排序</label>
                                <input type="text" name="sort" class="form-control " placeholder="排序" value="{{ old('sort',$case->sort) }}">
                                @if ($errors->has('sort')) <p class="help-block">{{ $errors->first('sort') }}</p> @endif
                            </div>

                            <div class="form-group @if ($errors->first('status')) has-error @endif">
                                <label>审核状态</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="status" value="0" @if ($case->status == 0) checked @endif  /> 待审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="status" value="1" @if ($case->status == 1) checked @endif /> 已审核
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
        </div>
    </section>
@endsection

@section('script')
    <script type="text/javascript">
        $(function(){
            set_active_menu('manage_review',"{{ route('admin.review.product.index') }}");
            $('input[type=radio][name=type]').change(function() {
                console.log(this.value);
                if (this.value == 'pdf' || this.value == 'image') {
                    $('#upload_file').css('display','block');
                    $('#link_url').css('display','none');
                } else {
                    $('#upload_file').css('display','none');
                    $('#link_url').css('display','block');
                }
            });
        });
    </script>
@endsection
