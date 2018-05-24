@extends('admin/public/layout')

@section('title')新建微信公众号@endsection

@section('content')
    <section class="content-header">
        <h1>
            新建微信公众号
            <small>添加微信公众号</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form id="article_form" method="POST" role="form" enctype="multipart/form-data" action="{{ route('admin.scraper.wechat.author.store') }}">
                        <input type="hidden" id="editor_token" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" id="tags" name="tags" value="" />
                        <div class="box-body">
                            <div class="form-group @if($errors->has('name')) has-error @endif ">
                                <label for="wx_hao">微信公众号id(精确匹配):</label>
                                <input id="wx_hao" type="text" name="wx_hao"  class="form-control input-lg" placeholder="如:taobaoguijiaoqi" value="{{ old('wx_hao','') }}" />
                                @if($errors->has('wx_hao')) <p class="help-block">{{ $errors->first('wx_hao') }}</p> @endif
                            </div>

                        </div>

                        <div class="box-footer">
                                <button type="submit" class="btn btn-primary editor-submit">创建微信公众号</button>
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