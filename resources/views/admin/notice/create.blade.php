@extends('admin/public/layout')

@section('content')
    <section class="content-header">
        <h1>
            公告管理
            <small>添加公告</small>
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
                    <form role="form" name="addForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.notice.store') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group">
                                <label>公告标题</label>
                                <input type="text" name="subject" class="form-control "  placeholder="公告标题" value="{{ old('subject','') }}">
                            </div>
                            <div class="form-group">
                                <label>移动端链接地址</label>
                                <input type="text" name="url_mobile" class="form-control "  placeholder="http://www.inwehub.com" value="{{ old('url','') }}">
                            </div>
                            <div class="form-group">
                                <label>网站链接地址</label>
                                <input type="text" name="url_www" class="form-control "  placeholder="http://www.inwehub.com" value="{{ old('url','') }}">
                            </div>
                            <div class="form-group">
                                <label>封面图片</label>
                                <input type="file" name="img_url" />
                            </div>
                            <div class="form-group">
                                <label>公告排序(越大越靠前)</label>
                                <input type="text" name="sort" class="form-control "  placeholder="http://www.inwehub.com" value="{{ old('sort',$sort) }}">
                            </div>
                            <div class="form-group">
                                <label>状态</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="status" value="1" checked /> 已审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="status" value="0" /> 待审核
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
        set_active_menu('operations',"{{ route('admin.notice.index') }}");
    </script>
@endsection
