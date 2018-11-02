@extends('admin.public.layout')
@section('title')新建产品点评@endsection
@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>
            新建产品点评
            <small>新建产品点评</small>
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
                    <form role="form" name="editForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.review.submission.store') }}">
                        <input type="hidden" id="tags" name="tags" value="{{ $tag->id }}" />
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group">
                                <label>标题</label>
                                <textarea name="title" class="form-control" placeholder="标题" style="height: 200px;">{{ old('title','') }}</textarea>
                            </div>

                            <div class="form-group">
                                <label>评分(0~5)</label>
                                <input type="number" name="rate_star" class="form-control "  placeholder="评分" value="{{ old('rate_star',0 ) }}">
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <select id="select_tags" name="select_tags" class="form-control" multiple="multiple" >
                                            <option value="{{ $tag->id }}" selected="selected">{{ $tag->name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>封面图片</label>
                                <input type="file" name="img_url" />
                            </div>

                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">新建</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script src="{{ asset('/static/js/select2/js/select2.min.js')}}"></script>
    <script src="{{ asset('/js/global.js')}}"></script>
    <script type="text/javascript">
        set_active_menu('manage_review',"{{ route('admin.review.submission.index') }}");
    </script>
@endsection
