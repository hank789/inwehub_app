@extends('admin/public/layout')
@section('title')圈子管理@endsection
@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>
            圈子管理
            <small>编辑圈子信息</small>
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
                    <form role="form" name="editForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.group.update',['id'=>$group->id]) }}">
                        <input name="_method" type="hidden" value="POST">
                        <input type="hidden" id="author_id" name="author_id" value="{{ $group->user_id }}" />
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group @if ($errors->first('name')) has-error @endif">
                                <label>圈子名称</label>
                                <input type="text" name="name" class="form-control "  placeholder="圈子名称" value="{{ old('name',$group->name) }}">
                                @if ($errors->first('name'))
                                    <span class="help-block">{{ $errors->first('name') }}</span>
                                @endif
                            </div>

                            <div class="form-group">
                                <label>圈子logo</label>
                                <input type="file" name="img_url" />
                                <div style="margin-top: 10px;">
                                    <img src="{{ old('img_url',$group->logo) }}" width="100"/>
                                </div>
                            </div>

                            <div class="form-group @if ($errors->first('author_id')) has-error @endif">
                                <label for="author_id_select" class="control-label">圈主</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select id="author_id_select" name="author_id_select" class="form-control">
                                            <option value="{{ $group->user_id }}" selected> {{ $group->user_id?'<span><img style="width: 30px;height: 20px;" src="' .($group->user->avatar) .'" class="img-flag" />' . ($group->user->name).'</span>':'' }} </option>
                                        </select>
                                        @if ($errors->first('author_id'))
                                            <span class="help-block">{{ $errors->first('author_id') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group @if ($errors->has('description')) has-error @endif">
                                <label for="name">圈子描述</label>
                                <textarea name="description" class="form-control " placeholder="圈子描述">{{ old('description',$group->description) }}</textarea>
                                @if ($errors->has('description')) <p class="help-block">{{ $errors->first('description') }}</p> @endif
                            </div>

                            <div class="form-group @if ($errors->first('public')) has-error @endif">
                                <label>公开</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="public" value="0" @if($group->public===0) checked @endif /> 隐私
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="public" value="1" @if($group->public===1) checked @endif /> 公开
                                    </label>
                                </div>
                            </div>

                            <div class="form-group @if ($errors->first('audit_status')) has-error @endif">
                                <label>审核状态</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="audit_status" value="0" @if($group->audit_status===0) checked @endif /> 未审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="audit_status" value="1" @if($group->audit_status===1) checked @endif /> 已审核
                                    </label>
                                    <label>
                                        <input type="radio" name="audit_status" value="2" @if($group->audit_status===2) checked @endif /> 审核失败
                                    </label>
                                    <label>
                                        <input type="radio" name="audit_status" value="4" @if($group->audit_status===4) checked @endif /> 已关闭
                                    </label>
                                    <label>
                                        <input type="radio" name="audit_status" value="3" @if($group->audit_status===3) checked @endif /> 系统圈子(权限为隐私)
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>审核失败的原因</label>
                                <textarea class="form-control" name="failed_reason" placeholder="仅审核失败的情况下填写">{{ $group->failed_reason }}</textarea>
                                @if ($errors->has('failed_reason')) <p class="help-block">{{ $errors->first('failed_reason') }}</p> @endif
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
        set_active_menu('manage_group',"{{ route('admin.group.index') }}");
        $(function(){
            $("#author_id_select").select2({
                theme:'bootstrap',
                placeholder: "圈主",
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
