@extends('admin/public/layout')

@section('content')
    <section class="content-header">
        <h1>
            首页问答推荐管理
            <small>添加首页问答推荐</small>
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
                    <form role="form" name="addForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.operate.recommendQa.store') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group">
                                <label>推荐标题</label>
                                <input type="text" name="subject" class="form-control "  placeholder="推荐标题" value="{{ old('subject','') }}">
                            </div>
                            <div class="form-group">
                                <label>推荐提问者姓名</label>
                                <input type="text" name="user_name" class="form-control "  placeholder="张三" value="{{ old('user_name','') }}">
                            </div>
                            <div class="form-group">
                                <label>推荐提问者头像地址</label>
                                <input type="text" name="user_avatar_url" class="form-control "  placeholder="http://inwehub-test.oss-cn-zhangjiakou.aliyuncs.com/media/16/user_origin_10.jpg" value="{{ old('user_avatar_url','') }}">
                            </div>
                            <div class="form-group">
                                <label>问题价格</label>
                                <input type="text" name="price" class="form-control "  placeholder="188" value="{{ old('price','') }}">
                            </div>
                            <div class="form-group">
                                <label>排序</label>
                                <input type="text" name="sort" class="form-control "  placeholder="请输入整数，小的排前面" value="{{ old('sort','') }}">
                            </div>
                            <div class="form-group">
                                <label>类型</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="type" value="1" checked /> 提问
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="type" value="2" /> 回答
                                    </label>
                                </div>
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
        set_active_menu('operations',"{{ route('admin.operate.recommendQa.index') }}");
    </script>
@endsection
