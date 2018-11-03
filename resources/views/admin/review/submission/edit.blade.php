@extends('admin.public.layout')
@section('title')编辑产品点评@endsection
@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>
            产品点评编辑
            <small>编辑产品点评</small>
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
                        <input type="hidden" id="tags" name="tags" value="{{ $submission->tags->implode('id',',') }}" />
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group">
                                <label>标题</label>
                                <textarea name="title" class="form-control" placeholder="标题" style="height: 200px;">{{ old('title',$submission->title) }}</textarea>
                            </div>

                            <div class="form-group">
                                <label>评分(0~5)</label>
                                <input type="number" name="rate_star" class="form-control "  placeholder="评分" value="{{ old('rate_star',$submission->rate_star ) }}">
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <select id="select_tags" name="select_tags" class="form-control" >
                                        @foreach($submission->tags as $tag)
                                            <option value="{{ $tag->id }}" selected="selected">{{ $tag->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group @if ($errors->first('author_id')) has-error @endif">
                                <label for="author_id_select" class="control-label">发布者</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <span><img style="width: 30px;height: 20px;" src="{{  $submission->owner->avatar }}" class="img-flag" />{{$submission->owner->name}}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>封面图片</label>
                                <input type="file" name="img_url" />
                                <div style="margin-top: 10px;">
                                    <img src="{{ old('img_url',is_array($submission->data['img'])?($submission->data['img']?$submission->data['img'][0]:''):$submission->data['img']) }}" width="100"/>
                                </div>
                            </div>

                            <div class="form-group @if ($errors->first('status')) has-error @endif">
                                <label>审核状态</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="status" value="0" @if($submission->status==0) checked @endif /> 待审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="status" value="1" @if($submission->status==1) checked @endif /> 已审核
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
    <script src="{{ asset('/js/global.js')}}"></script>
    <script type="text/javascript">
        set_active_menu('manage_review',"{{ route('admin.review.submission.index') }}");
    </script>
@endsection
