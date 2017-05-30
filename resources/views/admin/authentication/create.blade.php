@extends('admin/public/layout')
@section('title')新建专家认证信息@endsection

@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection

@section('content')
    <section class="content-header">
        <h1>新建专家认证信息</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form role="form" name="editForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.authentication.store') }}">
                        <input name="_method" type="hidden" value="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" id="skill_tags" name="skill_tags" value="" />

                        <div class="box-body">

                            <div class="form-group @if($errors->has('user_id')) has-error @endif">
                                <label>用户ID</label>
                                <input type="text" name="user_id" class="form-control " placeholder="用户ID" value="{{ old('user_id','') }}">
                                @if($errors->has('user_id')) <p class="help-block">{{ $errors->first('user_id') }}</p> @endif
                            </div>

                            <div class="form-group @if ($errors->first('skill_tags')) has-error @endif">
                                <label for="select_skill_tags" class="control-label">擅长领域</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select id="select_skill_tags" name="select_skill_tags" class="form-control" multiple="multiple" >

                                        </select>
                                        @if ($errors->first('skill_tags'))
                                            <span class="help-block">{{ $errors->first('skill_tags') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>状态</label>
                                <span class="text-muted">(禁用后前台不会显示)</span>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="status" value="0"  /> 待审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="status" value="1"  /> 通过审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="status" value="4" /> 审核失败
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>审核失败的原因</label>
                                <textarea class="form-control" name="failed_reason" placeholder="仅审核失败的情况下填写"></textarea>
                            </div>

                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">保存</button>
                            <button type="reset" class="btn btn-success">重置</button>
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
        $(function(){
            set_active_menu('manage_user',"{{ route('admin.authentication.index') }}");
            $("#select_skill_tags").select2({
                theme:'bootstrap',
                placeholder: "擅长领域",
                ajax: {
                    url: '/manager/ajax/loadTags',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            word: params.term,
                            type: 1
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                minimumInputLength:2,
                tags:false
            });

            $("#select_skill_tags").change(function(){
                $("#skill_tags").val($("#select_skill_tags").val());
            });

        });
    </script>
@endsection