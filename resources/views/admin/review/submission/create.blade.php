@extends('admin.public.layout')
@section('title')新建产品点评@endsection
@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>
            新建产品点评
            <small>新建产品点评</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="#">Tables</a></li>
            <li class="active">Simple</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                @include('admin/public/error')
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">基本信息</h3>
                    </div>
                    <form role="form" name="editForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.review.submission.store') }}">
                        <input type="hidden" id="tags" name="tags" value="{{ $tag->id }}" />
                        <input type="hidden" id="author_id" name="author_id" value="" />
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group">
                                <label>标题</label>
                                <textarea name="title" class="form-control" placeholder="标题" style="height: 200px;">{{ old('title','') }}</textarea>
                            </div>

                            <div class="form-group">
                                <label>评分(0~5)</label>
                                <input type="number" name="rate_star" class="form-control "  placeholder="评分" value="{{ old('rate_star',0 ) }}">
                            </div>
                            <div class="form-group">
                                <label for="author_id_select" class="control-label">产品</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                    <select id="select_tags" name="select_tags" class="form-control" >
                                            <option value="{{ $tag->id }}" selected="selected">{{ $tag->name }}</option>
                                    </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group @if ($errors->first('author_id')) has-error @endif">
                                <label for="author_id_select" class="control-label">发布者</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select id="author_id_select" name="author_id_select" class="form-control">
                                        </select>
                                        @if ($errors->first('author_id'))
                                            <span class="help-block">{{ $errors->first('author_id') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>封面图片</label>
                                <input type="file" name="img_url" />
                            </div>

                            <div class="form-group @if ($errors->first('hide')) has-error @endif">
                                <label>是否匿名</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="hide" value="1"  /> 匿名发布
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="hide" value="0" checked /> 公开
                                    </label>
                                </div>
                            </div>

                            <div class="form-group @if ($errors->first('status')) has-error @endif">
                                <label>审核状态</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="status" value="0"  /> 待审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="status" value="1" checked /> 已审核
                                    </label>
                                </div>
                            </div>


                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">新建</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script src="{{ asset('/static/js/select2/js/select2.min.js')}}"></script>
    <script src="{{ asset('/js/global.js')}}"></script>
    <script type="text/javascript">
        set_active_menu('manage_review',"{{ route('admin.review.submission.index') }}");
        $(function(){
            $("#author_id_select").select2({
                theme:'bootstrap',
                placeholder: "点评发布者",
                templateResult: function(state) {
                    if (!state.id) {
                        return state.text;
                    }
                    return $('<span><img style="width: 30px;height: 20px;" src="' + state.avatar + '" class="img-flag" /> ' + state.name + '</span>');
                },
                templateSelection: function (state) {
                    console.log(state.text);
                    if (!state.id) return state.text; // optgroup
                    if (state.text) return $(state.text);
                    return $('<span><img style="width: 30px;height: 20px;" src="' + state.avatar + '" class="img-flag" /> ' + (state.name || state.text) + '</span>');
                },
                ajax: {
                    url: '/manager/ajax/loadUsers',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            word: params.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                minimumInputLength:1,
                tags:false
            });

            $("#author_id_select").change(function(){
                $("#author_id").val($("#author_id_select").val());
            });
        })
    </script>
@endsection
