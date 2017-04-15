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
                    <form id="article_form" method="POST" role="form" enctype="multipart/form-data" action="{{ route('admin.inwehub.news.update',['id'=>$news->_id]) }}">
                        <input type="hidden" id="editor_token" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group @if($errors->has('title')) has-error @endif ">
                                <label for="title">标题:</label>
                                <input id="title" type="text" name="title"  class="form-control input-lg" placeholder="" value="{{ old('title',$news->title) }}" />
                                @if($errors->has('title')) <p class="help-block">{{ $errors->first('title') }}</p> @endif
                            </div>

                            <div class="form-group">
                                <label for="author">作者：</label>
                                <input id="author" type="text" name="author"  class="form-control input-lg" placeholder="" value="{{ old('author',$news->author) }}" />
                            </div>
                            <div class="form-group">
                                <label for="site_name">站点名字：</label>
                                <input id="site_name" type="text" name="site_name"  class="form-control input-lg" placeholder="" value="{{ old('site_name',$news->site_name) }}" />
                            </div>

                            <div class="form-group">
                                <label for="content_url">网站url：</label>
                                <input id="content_url" type="text" name="content_url"  class="form-control input-lg" placeholder="" value="{{ old('content_url',$news->content_url) }}" />
                            </div>
                            <div class="form-group">
                                <label for="mobile_url">手机端url：</label>
                                <input id="mobile_url" type="text" name="mobile_url"  class="form-control input-lg" placeholder="默认与网站url一致" value="{{ old('mobile_url',$news->mobile_url) }}" />
                            </div>

                            <div class="form-group">
                                <label for="editor">摘要：</label>
                                <textarea name="description" class="form-control" placeholder="200字以内的摘要">{{ old('description',$news->description) }}</textarea>
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
        set_active_menu('manage_inwehub',"{{ route('admin.inwehub.news.index') }}");
    </script>
@endsection