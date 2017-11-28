@extends('admin/public/layout')
@section('title')推送管理@endsection

@section('content')
    <section class="content-header">
        <h1>
            推送管理
            <small>编辑推送</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                @include('admin/public/error')
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">基本信息</h3>
                    </div>
                    <form role="form" name="editForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.operate.pushNotice.update',['id'=>$notice->id]) }}">
                        <input name="_method" type="hidden" value="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group">
                                <label>标题</label>
                                <input type="text" name="title" class="form-control "  placeholder="标题" value="{{ old('title',$notice->title) }}">
                            </div>

                            <div class="form-group">
                                <label>url地址</label>
                                <input type="text" name="url" class="form-control "  placeholder="http://read.ywhub.com/c/4/app-1" value="{{ old('url',$notice->url) }}">
                            </div>

                            <div class="form-group">
                                <label>文章类型</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="notification_type" value="1" @if($notice->notification_type===1) checked @endif /> 发现分享(url地址为发现分享id)
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="notification_type" value="2" @if($notice->notification_type===2) checked @endif /> 公告文章(外部url)
                                    </label>
                                    <label>
                                        <input type="radio" name="notification_type" value="3" @if($notice->notification_type===3) checked @endif /> app内页(内页地址)
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>发送时间</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="is_push" value="2" checked /> 稍后再发(请先在列表页面测试推送没问题后再正式发送)
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
        set_active_menu('operations',"{{ route('admin.operate.pushNotice.index') }}");
    </script>
@endsection
