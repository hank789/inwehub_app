@extends('admin.public.layout')
@section('title')发现分享编辑@endsection
@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>
            发现分享编辑
            <small>编辑发现分享</small>
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
                    <form role="form" name="editForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.operate.article.update',['id'=>$submission->id]) }}">
                        <input name="_method" type="hidden" value="PUT">
                        <input type="hidden" id="author_id" name="author_id" value="{{ $submission->author_id }}" />
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group">
                                <label>标题</label>
                                <span>{{ $submission->title }}</span>
                            </div>

                            <div class="form-group">
                                <label>类型</label>
                                <span>{{ $submission->type }}</span>
                            </div>

                            <div class="form-group @if ($errors->first('author_id')) has-error @endif">
                                <label for="author_id_select" class="control-label">专栏作者</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select id="author_id_select" name="author_id_select" class="form-control">
                                            <option value="{{ $submission->author_id }}" selected> {{ $submission->author_id?'<span><img style="width: 30px;height: 20px;" src="' .($submission->author->avatar) .'" class="img-flag" />' . ($submission->author->name).'</span>':'' }} </option>
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
                                <div style="margin-top: 10px;">
                                    <img src="{{ old('img_url',is_array($submission->data['img'])?($submission->data['img']?$submission->data['img'][0]:''):$submission->data['img']) }}" width="100"/>
                                </div>
                            </div>

                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">保存</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script src="{{ asset('/static/js/select2/js/select2.min.js')}}"></script>
    <script type="text/javascript">
        set_active_menu('operations',"{{ route('admin.operate.article.index') }}");
        $(function(){
            $("#author_id_select").select2({
                theme:'bootstrap',
                placeholder: "专栏作者",
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
