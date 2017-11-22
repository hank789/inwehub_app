@extends('admin/public/layout')

@section('content')
    <section class="content-header">
        <h1>
            编辑服务
            <small>编辑服务</small>
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
                    <form role="form" name="editForm" method="POST" action="{{ route('admin.company.service.update',['id'=>$service->id]) }}">
                        <input name="_method" type="hidden" value="PUT">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group">
                                <label>标题</label>
                                <input type="text" name="title" class="form-control "  placeholder="服务标题" value="{{ old('subject',$service->title) }}">
                            </div>
                            <div class="form-group">
                                <label>幻灯片图片地址</label>
                                <input type="text" name="img_url_slide" class="form-control "  placeholder="http://www.inwehub.com" value="{{ old('img_url_slide',$service->img_url_slide) }}">
                            </div>
                            <div class="form-group">
                                <label>列表图片地址</label>
                                <input type="text" name="img_url_list" class="form-control "  placeholder="http://www.inwehub.com" value="{{ old('img_url_list',$service->img_url_list) }}">
                            </div>
                            <div class="form-group">
                                <label>排序</label>
                                <input type="text" name="sort" class="form-control "  placeholder="http://www.inwehub.com" value="{{ old('sort',$service->sort) }}">
                            </div>
                            <div class="form-group">
                                <label>状态</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="audit_status" value="1" @if($service->audit_status===1) checked @endif /> 已审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="audit_status" value="0" @if($service->audit_status===0) checked @endif /> 待审核
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
    <script type="text/javascript">
        set_active_menu('manage_company',"{{ route('admin.company.service.index') }}");
    </script>
@endsection
