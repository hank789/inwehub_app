@extends('admin/public/layout')
@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>
            编辑企业信息
            <small>编辑企业信息</small>
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
                    <form role="form" name="editForm" method="POST" action="{{ route('admin.company.data.update',['id'=>$company->id]) }}">
                        <input name="_method" type="hidden" value="PUT">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" id="tags_id" name="tags_id" value="" />
                        <div class="box-body">
                            <div class="form-group">
                                <label>企业名称</label>
                                <input type="text" name="name" class="form-control "  placeholder="" value="{{ old('subject',$company->name) }}">
                            </div>
                            <div class="form-group">
                                <label>Logo图片地址</label>
                                <input type="text" name="logo" class="form-control "  placeholder="" value="{{ old('logo',$company->logo) }}">
                            </div>
                            <div class="form-group">
                                <label>所在省市</label>
                                <input type="text" name="address_province" class="form-control "  placeholder="http://www.inwehub.com" value="{{ old('address_province',$company->address_province) }}">
                            </div>
                            <div class="form-group">
                                <label>详细地址</label>
                                <input type="text" name="address_detail" class="form-control "  placeholder="http://www.inwehub.com" value="{{ old('sort',$company->address_detail) }}">
                            </div>
                            <div class="form-group">
                                <label>经度</label>
                                <input type="text" name="longitude" class="form-control "  placeholder="73.12" value="{{ old('longitude',$company->longitude) }}">
                            </div>
                            <div class="form-group">
                                <label>纬度</label>
                                <input type="text" name="latitude" class="form-control "  placeholder="43.12" value="{{ old('latitude',$company->latitude) }}">
                            </div>
                            <div class="form-group @if ($errors->first('tags')) has-error @endif">
                                <label for="tags" class="control-label">标签</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select id="tags" name="tags" class="form-control" multiple="multiple" >
                                            @foreach( $company->tags() as $tag)
                                                <option value="{{ $tag->id }}" selected>{{ $tag->name }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->first('tags'))
                                            <span class="help-block">{{ $errors->first('tags') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>状态</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="audit_status" value="1" @if($company->audit_status===1) checked @endif /> 已审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="audit_status" value="0" @if($company->audit_status===0) checked @endif /> 待审核
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
        set_active_menu('manage_company',"{{ route('admin.company.data.index') }}");
        $(function(){
            $("#tags").select2({
                theme:'bootstrap',
                placeholder: "标签",
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

            $("#tags").change(function(){
                $("#tags_id").val($("#tags").val());
            });
        });
    </script>
@endsection
