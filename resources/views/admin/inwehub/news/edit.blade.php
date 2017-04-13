@extends('admin/public/layout')

@section('title')编辑新闻@endsection

@section('content')
    <section class="content-header">
        <h1>
             编辑新闻
            <small>编辑新闻</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form id="article_form" method="POST" role="form" enctype="multipart/form-data" action="{{ route('admin.inwehub.news.update',['id'=>$news->id]) }}">
                        <input type="hidden" id="editor_token" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group @if($errors->has('title')) has-error @endif ">
                                <label for="title">标题:</label>
                                <input id="title" type="text" name="title"  class="form-control input-lg" placeholder="" value="{{ old('title',$news->title) }}" />
                                @if($errors->has('title')) <p class="help-block">{{ $errors->first('title') }}</p> @endif
                            </div>

                            <div class="form-group">
                                <label for="author_name">作者：</label>
                                <input id="author_name" type="text" name="author_name"  class="form-control input-lg" placeholder="" value="{{ old('author_name',$news->author_name) }}" />
                            </div>
                            <div class="form-group">
                                <label for="site_name">站点名字：</label>
                                <input id="site_name" type="text" name="site_name"  class="form-control input-lg" placeholder="" value="{{ old('site_name',$news->site_name) }}" />
                            </div>

                            <div class="form-group">
                                <label for="url">网站url：</label>
                                <input id="url" type="text" name="url"  class="form-control input-lg" placeholder="" value="{{ old('url',$news->url) }}" />
                            </div>
                            <div class="form-group">
                                <label for="mobile_url">手机端url：</label>
                                <input id="mobile_url" type="text" name="mobile_url"  class="form-control input-lg" placeholder="默认与网站url一致" value="{{ old('mobile_url',$news->mobile_url) }}" />
                            </div>


                        </div>

                        <div class="box-footer">
                            <div class="col-xs-12 col-md-1">
                                <button type="submit" class="btn btn-primary pull-right editor-submit">提交修改</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('script')
    <script type="text/javascript">
        set_active_menu('manage_inwehub',"{{ route('admin.inwehub.news.index') }}");
    </script>
@endsection