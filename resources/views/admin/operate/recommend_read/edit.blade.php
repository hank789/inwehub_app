@extends('admin/public/layout')
@section('title')精选推荐管理@endsection

@section('content')
    <section class="content-header">
        <h1>
            精选推荐管理
            <small>编辑精选推荐</small>
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
                                <input type="text" name="title" class="form-control "  placeholder="标题" value="{{ old('title',$recommendation->data['title']) }}">
                            </div>

                            <div class="form-group">
                                <label>类型</label>
                                <span>{{ $recommendation->getReadTypeName() }}</span>
                            </div>

                            <div class="form-group">
                                <label>地址</label>
                                <span><a href="{{ $recommendation->getWebUrl() }}" target="_blank">{{ $recommendation->getWebUrl() }}</a></span>
                            </div>

                            <div class="form-group">
                                <label>封面图片</label>
                                <input type="file" name="img_url" />
                                <div style="margin-top: 10px;">
                                    <img src="{{ old('img_url',is_array($recommendation->data['img'])?($recommendation->data['img']?$recommendation->data['img'][0]:''):$recommendation->data['img']) }}" width="100"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>排序</label>
                                <input type="text" name="recommend_sort" class="form-control "  placeholder="请输入整数，大的排前面" value="{{ old('recommend_sort',$recommendation->sort ? : $recommendation->id ) }}">
                            </div>

                            <div class="form-group">
                                <label>审核状态</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="recommend_status" value="0" @if($recommendation->audit_status===0) checked @endif /> 推荐未审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="recommend_status" value="1" @if($recommendation->audit_status===1) checked @endif /> 推荐已审核
                                    </label>
                                    <label>
                                        <input type="radio" name="recommend_status" value="2" @if($recommendation->audit_status===2) checked @endif /> 推荐已拒绝
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
