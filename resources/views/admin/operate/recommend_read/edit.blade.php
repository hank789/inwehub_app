@extends('admin/public/layout')
@section('title')首页阅读推荐管理@endsection

@section('content')
    <section class="content-header">
        <h1>
            首页阅读推荐管理
            <small>编辑阅读推荐</small>
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
                    <form role="form" name="editForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.operate.recommendRead.update',['id'=>$recommendation->id]) }}">
                        <input name="_method" type="hidden" value="PUT">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group">
                                <label>标题</label>
                                <input type="text" name="title" class="form-control "  placeholder="标题" value="{{ old('title',$recommendation->title) }}">
                            </div>

                            <div class="form-group">
                                <label>频道</label>
                                <span>{{ $recommendation->category_name }}</span>
                            </div>

                            <div class="form-group">
                                <label>封面图片地址</label>
                                <input type="text" name="img_url" class="form-control "  placeholder="http://inwehub-test.oss-cn-zhangjiakou.aliyuncs.com/media/16/user_origin_10.jpg" value="{{ old('img_url',$recommendation->data['img']) }}">
                            </div>
                            <div class="form-group">
                                <label>排序</label>
                                <input type="text" name="recommend_sort" class="form-control "  placeholder="请输入整数，大的排前面" value="{{ old('recommend_sort',$recommendation->recommend_sort ) }}">
                            </div>

                            <div class="form-group">
                                <label>状态</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="recommend_status" value="1" @if($recommendation->recommend_status===1) checked @endif /> 推荐未审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="recommend_status" value="2" @if($recommendation->recommend_status===2) checked @endif /> 推荐已审核
                                    </label>
                                    <label>
                                        <input type="radio" name="recommend_status" value="0" @if($recommendation->recommend_status===3) checked @endif /> 推荐已拒绝
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
        set_active_menu('operations',"{{ route('admin.operate.recommendRead.index') }}");
    </script>
@endsection
