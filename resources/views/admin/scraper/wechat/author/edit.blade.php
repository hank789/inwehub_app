@extends('admin/public/layout')

@section('title')更新微信公众号@endsection

@section('content')
    <section class="content-header">
        <h1>
            更新微信公众号
            <small>更新微信公众号</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form id="article_form" method="POST" role="form" enctype="multipart/form-data" action="{{ route('admin.scraper.wechat.author.update',['id'=>$author->_id]) }}">
                        <input name="_method" type="hidden" value="POST">
                        <input type="hidden" id="editor_token" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group @if($errors->has('name')) has-error @endif ">
                                <label for="wx_hao">微信公众号id(精确匹配):</label>
                                <label>{{ $author->wx_hao }}</label>
                            </div>
                            <div class="form-group @if($errors->has('group_id')) has-error @endif ">
                                <label for="wx_hao">圈子id:</label>
                                <input id="group_id" type="text" name="group_id"  class="form-control input-lg" placeholder="该公众号文章所属的圈子id" value="{{ old('group_id',$author->group_id) }}" />
                                @if($errors->has('group_id')) <p class="help-block">{{ $errors->first('group_id') }}</p> @endif
                            </div>

                            <div class="form-group @if ($errors->first('audit_status')) has-error @endif">
                                <label>审核状态</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="audit_status" value="0" @if($author->status===0) checked @endif /> 待审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="audit_status" value="1" @if($author->status===1) checked @endif /> 已审核
                                    </label>
                                </div>
                            </div>

                        </div>

                        <div class="box-footer">
                                <button type="submit" class="btn btn-primary editor-submit">提交修改</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('script')
    <script type="text/javascript">
        set_active_menu('manage_scraper',"{{ route('admin.scraper.wechat.author.index') }}");
    </script>
@endsection