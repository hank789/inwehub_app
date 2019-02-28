@extends('admin.public.layout')
@section('title')添加点评官方回复@endsection

@section('content')
    <section class="content-header">
        <h1>
            点评官方回复
            <small>编辑点评官方回复</small>
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
                    <form role="form" name="editForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.review.submission.storeOfficialReply',['id'=>$submission->id]) }}">
                        <input type="hidden" id="comment_id" name="comment_id" value="{{ $comment?$comment->id:0 }}" />
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group">
                                <label>点评内容</label>
                                <p>{{ $submission->title }}</p>
                            </div>
                            <div class="form-group">
                                <label>官方回复内容：</label>
                                <textarea name="content" class="form-control" placeholder="回复内容" style="height: 200px;">{{ old('content',$comment?$comment->content:'') }}</textarea>
                            </div>

                            <div class="form-group @if ($errors->first('status')) has-error @endif">
                                <label>审核状态</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="status" value="0" @if($comment && $comment->status==0) checked @endif /> 待审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="status" value="1" @if(($comment && $comment->status==1) || (!$comment)) checked @endif /> 已审核
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
    <script src="{{ asset('/js/global.js')}}"></script>
    <script type="text/javascript">
        set_active_menu('manage_review',"{{ route('admin.review.submission.index') }}");
    </script>
@endsection
