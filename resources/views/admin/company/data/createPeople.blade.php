@extends('admin/public/layout')
@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>
            添加企业相关人员
            <small>添加企业相关人员</small>
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
                    <form role="form" name="addForm" method="POST" action="{{ route('admin.company.data.storePeople') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" id="user_id" name="user_id" value="0" />
                        <div class="box-body">
                            <div class="form-group">
                                <label>公司id</label>
                                <input type="text" name="company_data_id" class="form-control "  placeholder="公司id" value="{{ old('company_data_id',$data_id) }}">
                            </div>

                            <div class="form-group @if ($errors->first('user_id')) has-error @endif">
                                <label for="author_id_select" class="control-label">用户</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select id="user_id_select" name="user_id_select" class="form-control">
                                        </select>
                                        @if ($errors->first('user_id'))
                                            <span class="help-block">{{ $errors->first('user_id') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>在职状态</label>
                                <label>
                                    <input type="radio" name="status" value="1" checked /> 在职
                                </label>&nbsp;&nbsp;
                                <label>
                                    <input type="radio" name="status" value="2" /> 项目
                                </label>
                                <label>
                                    <input type="radio" name="status" value="3" /> 离职
                                </label>
                            </div>
                            <div class="form-group">
                                <label>审核状态</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="audit_status" value="1" checked /> 已审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="audit_status" value="0" /> 待审核
                                    </label>
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
        set_active_menu('manage_company',"{{ route('admin.company.data.people') }}");
        $(function(){
            $("#user_id_select").select2({
                theme:'bootstrap',
                placeholder: "用户",
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

            $("#user_id_select").change(function(){
                $("#user_id").val($("#user_id_select").val());
            });
        })
    </script>
@endsection
