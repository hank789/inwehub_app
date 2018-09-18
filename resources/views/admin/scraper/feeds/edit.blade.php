@extends('admin/public/layout')

@section('title')编辑数据源@endsection

@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection

@section('content')
    <section class="content-header">
        <h1>
             编辑数据源
            <small>编辑数据源</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form id="article_form" method="POST" role="form" enctype="multipart/form-data" action="{{ route('admin.scraper.feeds.update',['id'=>$feeds->id]) }}">
                        <input type="hidden" id="editor_token" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="group_id" id="group_id" value="{{ $feeds->group_id }}" />
                        <input type="hidden" id="user_id" name="user_id" value="{{ $feeds->user_id }}" />
                        <div class="box-body">
                            <div class="form-group @if($errors->has('name')) has-error @endif ">
                                <label for="name">站点名:</label>
                                <input id="name" type="text" name="name"  class="form-control input-lg" placeholder="" value="{{ old('name',$feeds->name) }}" />
                                @if($errors->has('name')) <p class="help-block">{{ $errors->first('name') }}</p> @endif
                            </div>

                            <div class="form-group @if($errors->has('group_id')) has-error @endif ">
                                <label for="wx_hao">圈子:</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select id="select_group_id" name="select_group_id" class="form-control">
                                            <option value="0" {{ $feeds->group_id == 0 ? 'selected':'' }}></option>
                                            @foreach($groups as $group)
                                                <option value="{{ $group['id'] }}" {{ $feeds->group_id == $group['id'] ? 'selected':'' }}>{{ $group['name'] }}</option>
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
                                            <option value="{{ $feeds->user_id }}" selected> {{ $feeds->user_id?'<span><img style="width: 30px;height: 20px;" src="' .($feeds->user->avatar) .'" class="img-flag" />' . ($feeds->user->name).'</span>':'' }} </option>
                                        </select>
                                        @if ($errors->first('user_id'))
                                            <span class="help-block">{{ $errors->first('user_id') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group @if($errors->has('keywords')) has-error @endif ">
                                <label for="keywords">关键词(多个以"|"隔开):</label>
                                <input id="keywords" type="text" name="keywords"  class="form-control input-lg" placeholder="关键词，多个以'|'隔开" value="{{ old('keywords',$feeds->keywords) }}" />
                                @if($errors->has('keywords')) <p class="help-block">{{ $errors->first('keywords') }}</p> @endif
                            </div>

                            <div class="form-group">
                                <label for="source_type">源类型：</label>
                                <div class="radio">
                                    <label><input type="radio" name="source_type" value="1" @if ( $feeds->source_type == 1) checked @endif >RSS</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label><input type="radio" name="source_type" value="2" @if ( $feeds->source_type == 2) checked @endif>ATOM</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="is_auto_publish">是否自动发布文章：</label>
                                <div class="radio">
                                    <label><input type="radio" name="is_auto_publish" value="0" @if ( $feeds->is_auto_publish == 0) checked @endif >审核后发布</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label><input type="radio" name="is_auto_publish" value="1" @if ( $feeds->is_auto_publish == 1) checked @endif>自动发布</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="source_link">数据源url：</label>
                                <input id="source_link" type="text" name="source_link"  class="form-control input-lg" placeholder="" value="{{ old('source_link',$feeds->source_link) }}" />
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
        set_active_menu('manage_scraper',"{{ route('admin.scraper.feeds.index') }}");
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
    </script>
@endsection