@extends('admin.public.layout')
@section('title')发现分享编辑@endsection

@section('content')
    <section class="content-header">
        <h1>
            发现分享编辑
            <small>编辑发现分享</small>
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
                    <form role="form" name="editForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.operate.article.update',['id'=>$submission->id]) }}">
                        <input name="_method" type="hidden" value="PUT">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group">
                                <label>标题</label>
                                <span>{{ $submission->title }}</span>
                            </div>

                            <div class="form-group">
                                <label>类型</label>
                                <span>{{ $submission->type }}</span>
                            </div>

                            <div class="form-group">
                                <label>专栏作者id</label>
                                <input type="text" name="author_id" class="form-control "  placeholder="值为0则不设专栏作者" value="{{ old('author_id',$submission->author_id) }}">
                            </div>

                            <div class="form-group">
                                <label>封面图片地址</label>
                                <input type="text" name="img_url" class="form-control "  placeholder="http://inwehub-test.oss-cn-zhangjiakou.aliyuncs.com/media/16/user_origin_10.jpg" value="{{ old('img_url',is_array($submission->data['img'])?($submission->data['img']?$submission->data['img'][0]:''):$submission->data['img']) }}">
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
        set_active_menu('operations',"{{ route('admin.operate.article.index') }}");
    </script>
@endsection
