@extends('admin/public/layout')

@section('title')更新微信公众号@endsection

@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection

@section('content')
    <section class="content-header">
        <h1>
            更新微信公众号
            <small>更新微信公众号</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form id="article_form" method="POST" role="form" enctype="multipart/form-data" action="{{ route('admin.scraper.wechat.author.update',['id'=>$author->_id]) }}">
                        <input name="_method" type="hidden" value="POST">
                        <input type="hidden" name="group_id" id="group_id" value="{{ $author->group_id }}" />
                        <input type="hidden" id="user_id" name="user_id" value="{{ $author->user_id }}" />
                        <input type="hidden" name="tagIds" id="tagIds" value="-1" />
                        <input type="hidden" id="editor_token" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group @if($errors->has('name')) has-error @endif ">
                                <label for="wx_hao">微信公众号id(精确匹配):</label>
                                <label>{{ $author->wx_hao }}</label>
                            </div>
                            <div class="form-group @if($errors->has('name')) has-error @endif ">
                                <label for="wx_hao">公众号名称:</label>
                                <label>{{ $author->name }}</label>
                            </div>
                            <div class="form-group @if($errors->has('group_id')) has-error @endif ">
                                <label for="wx_hao">圈子:</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select id="select_group_id" name="select_group_id" class="form-control">
                                            <option value="0" {{ $author->group_id == 0 ? 'selected':'' }}>不属于圈子</option>
                                            @foreach($groups as $group)
                                                <option value="{{ $group['id'] }}" {{ $author->group_id == $group['id'] ? 'selected':'' }}>{{ $group['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @if($errors->has('group_id')) <p class="help-block">{{ $errors->first('group_id') }}</p> @endif
                            </div>

                            <div class="form-group @if($errors->has('user_id')) has-error @endif ">
                                <label for="wx_hao">文章发布者:</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select id="author_id_select" name="author_id_select" class="form-control">
                                            <option value="{{ $author->user_id }}" selected> {{ $author->user_id?'<span><img style="width: 30px;height: 20px;" src="' .($author->user->avatar) .'" class="img-flag" />' . ($author->user->name).'</span>':'' }} </option>
                                        </select>
                                        @if ($errors->first('user_id'))
                                            <span class="help-block">{{ $errors->first('user_id') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="wx_hao">所属领域:</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                    <select id="select_tags_id" name="select_tags_id" class="form-control" multiple="multiple" >
                                        @foreach($author->tags as $tag)
                                            <option value="{{ $tag->id }}" selected="selected">{{ $tag->name }}</option>
                                        @endforeach
                                        @foreach($tags as $tag)
                                            <option value="{{ $tag['id'] }}">{{ $tag['text'] }}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="is_auto_publish">是否自动发布文章：</label>
                                <div class="radio">
                                    <label><input type="radio" name="is_auto_publish" value="0" @if ( $author->is_auto_publish == 0) checked @endif >审核后发布</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label><input type="radio" name="is_auto_publish" value="1" @if ( $author->is_auto_publish == 1) checked @endif>自动发布</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                </div>
                            </div>

                            <div class="form-group @if ($errors->first('audit_status')) has-error @endif">
                                <label>审核状态</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="audit_status" value="0" @if($author->status===0) checked @endif /> 待审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="audit_status" value="1" @if($author->status===1) checked @endif /> 已审核
                                    </label>
                                </div>
                            </div>

                        </div>

                        <div class="box-footer">
                                <button type="submit" class="btn btn-primary editor-submit">提交修改</button>
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
        set_active_menu('manage_scraper',"{{ route('admin.scraper.wechat.author.index') }}");
        $("#select_group_id").select2({
            theme:'bootstrap',
            placeholder: "选择圈子",
            tags:false
        });

        $("#select_group_id").change(function(){
            $("#group_id").val($("#select_group_id").val());
        });

        $("#author_id_select").select2({
            theme:'bootstrap',
            placeholder: "指定文章发布者",
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
            $("#user_id").val($("#author_id_select").val());
        });

        $("#select_tags_id").select2({
            theme:'bootstrap',
            placeholder: "领域"
        });

        $("#select_tags_id").change(function(){
            $("#tagIds").val($("#select_tags_id").val());
        });
    </script>
@endsection