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
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="topic_news_table">
                                            <thead>
                                            <tr>
                                                <th><input type="checkbox" class="checkbox-toggle" /></th>
                                                <th>ID</th>
                                                <th>标题</th>
                                                <th>发布时间</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($news as $item)
                                                <tr>
                                                    <td><input type="checkbox" name="news[]" value="{{ $item->_id }}" @if($item->topic_id == $article->id) checked @endif/></td>
                                                    <td>{{ $item->_id }}</td>
                                                    <td>{{ $item->title }}</td>
                                                    <td>{{ $item->date_time }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
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
@section('css')
    <link href="{{ asset('/static/css/datatables/dataTables.bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('script')
    <script src='{{ asset('/static/js/datatables/jquery.dataTables.min.js') }}' type="text/javascript"></script>
    <script src='{{ asset('/static/js/datatables/dataTables.bootstrap.min.js') }}' type="text/javascript"></script>

    <script type="text/javascript">
        set_active_menu('manage_inwehub',"{{ route('admin.inwehub.topic.index') }}");
        $(document).ready(function() {
            $('#topic_news_table').DataTable({
                "pageLength": 100,
                "order": [[ 3, 'desc' ]]
            });
        } );
    </script>
@endsection