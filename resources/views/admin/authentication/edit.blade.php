@extends('admin/public/layout')
@section('title')编辑专家认证信息@endsection

@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection

@section('content')
    <section class="content-header">
        <h1>编辑专家认证信息</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form role="form" name="editForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.authentication.update',['id'=>$authentication->user_id]) }}">
                        <input name="_method" type="hidden" value="PUT">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" id="industry_tags" name="industry_tags" value="" />


                        <div class="box-body">

                            <div class="form-group @if($errors->has('real_name')) has-error @endif">
                                <label>真实姓名</label>
                                <input type="text" name="real_name" class="form-control " placeholder="真实姓名" value="{{ old('real_name',$authentication->real_name) }}">
                                @if($errors->has('real_name')) <p class="help-block">{{ $errors->first('real_name') }}</p> @endif
                            </div>

                            <div class="form-group ">
                                <label for="time_friendly">性别</label>
                                <div class="radio">
                                    <label><input type="radio" name="gender" value="1" @if ( $authentication->gender === 1) checked @endif >男</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label><input type="radio" name="gender" value="2" @if ( $authentication->gender === 2) checked @endif >女</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label><input type="radio" name="gender" value="0" @if ( $authentication->gender === 0) checked @endif >保密</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="setting-city" class="control-label">所在城市</label>
                                <div class="row">
                                    <div class="col-sm-5">
                                        <select class="form-control" name="province" id="province">
                                            <option>请选择省份</option>
                                            @foreach($data['provinces'] as $key=>$province)
                                                <option value="{{ $key }}"  @if($authentication->province == $key) selected @endif>{{ $province }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-5">
                                        <select class="form-control" name="city" id="city">
                                            <option>请选择城市</option>
                                            @foreach($data['cities'] as $key=>$city)
                                                <option value="{{ $key }}" @if($authentication->city == $key) selected @endif >{{ $city }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group @if ($errors->has('title')) has-error @endif">
                                <label for="name">身份职业</label>
                                <input type="text" name="title" class="form-control " placeholder="身份职业" value="{{ old('title',$authentication->title) }}">
                                @if ($errors->has('title')) <p class="help-block">{{ $errors->first('title') }}</p> @endif
                            </div>


                            <div class="form-group @if ($errors->has('description')) has-error @endif">
                                <label for="name">自我介绍</label>
                                <textarea name="description" class="form-control " placeholder="自我介绍">{{ old('description',$authentication->description) }}</textarea>
                                @if ($errors->has('description')) <p class="help-block">{{ $errors->first('description') }}</p> @endif
                            </div>

                            <div class="form-group @if ($errors->first('industry_tags')) has-error @endif">
                                <label for="select_industry_tags" class="control-label">所在行业</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select id="select_industry_tags" name="select_industry_tags" class="form-control" multiple="multiple" >
                                            @foreach( $authentication->user->industryTags() as $tag)
                                                <option value="{{ $tag->id }}" selected>{{ $tag->name }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->first('industry_tags'))
                                            <span class="help-block">{{ $errors->first('industry_tags') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>状态</label>
                                <span class="text-muted">(禁用后前台不会显示)</span>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="status" value="0" @if($authentication->status === 0) checked @endif /> 待审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="status" value="1" @if($authentication->status === 1) checked @endif /> 通过审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="status" value="4" @if($authentication->status === 4 ) checked @endif /> 审核失败
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
            /*加载省份城市*/
            $("#province").change(function(){
                var province_id = $(this).val();
                $("#city").load("{{ url('manager/ajax/loadCities') }}/"+province_id);
            });
            $("#select_industry_tags").select2({
                theme:'bootstrap',
                placeholder: "所在行业",
                ajax: {
                    url: '/manager/ajax/loadTags',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            word: params.term,
                            type: 3
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

            $("#select_industry_tags").change(function(){
                $("#industry_tags").val($("#select_industry_tags").val());
            });
        });
    </script>
@endsection