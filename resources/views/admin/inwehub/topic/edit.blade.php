@extends('admin/public/layout')
@section('title')编辑话题@endsection
@section('content')
    <section class="content-header">
        <h1>
            话题编辑
            <small>修改话题信息</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab_base" data-toggle="tab" aria-expanded="false">话题信息</a></li>
                        <li><a href="#tab_news" data-toggle="tab" aria-expanded="true">选择新闻</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_base">
                            <form id="article_form" method="POST" role="form" enctype="multipart/form-data" action="{{ route('admin.inwehub.topic.update',['id'=>$article->id]) }}">
                                <input type="hidden" id="editor_token" name="_token" value="{{ csrf_token() }}">

                                <div class="form-group @if($errors->has('title')) has-error @endif ">
                                    <label for="title">文章标题:</label>
                                    <input id="title" type="text" name="title"  class="form-control input-lg" placeholder="我想起那天下午在夕阳下的奔跑,那是我逝去的青春" value="{{ old('title',$article->title) }}" />
                                    @if($errors->has('title')) <p class="help-block">{{ $errors->first('title') }}</p> @endif
                                </div>

                                <div class="form-group">
                                    <label for="editor">文章导读：</label>
                                    <textarea name="summary" class="form-control">{{ $article->summary }}</textarea>
                                </div>

                                <div class="row mt-20">
                                    <div class="col-xs-12 col-md-1">
                                        <input type="hidden" id="article_editor_content"  name="content" value="{{ $article->content }}"  />
                                        <button type="submit" class="btn btn-primary pull-right editor-submit">提交修改</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane" id="tab_news">
                            <form role="form" name="newsForm" method="POST" action="{{ route('admin.inwehub.topic.news.update',['id'=>$article->id]) }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="box-body">
                                    <h4>新闻列表</h4>
                                    <div class="form-group">
                                        @foreach($news as $item)
                                            <div class="col-xs-3">
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="news[]" value="{{ $item->id }}" @if($item->topic_id == $article->id) checked @endif/>
                                                        {{ $item->title }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary">保存</button>
                                    <button type="reset" class="btn btn-success">重置</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

@endsection
@section('script')
    <script type="text/javascript">
        set_active_menu('manage_inwehub',"{{ route('admin.inwehub.topic.index') }}");
    </script>
@endsection