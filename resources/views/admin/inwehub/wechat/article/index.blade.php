@extends('admin/public/layout')
@section('title')微信文章管理@endsection
@section('content')
    <section class="content-header">
        <h1>
            Inwehub微信文章管理
            <small>管理Inwehub的所有微信文章</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-2">
                                <div class="btn-group">
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除选中项" onclick="confirm_submit('item_form','{{  route('admin.inwehub.wechat.article.destroy') }}','确认删除选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-10">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.inwehub.wechat.article.index') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="user_id" placeholder="公众号ID" value="{{ $filter['author_id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="topic_id" placeholder="话题ID" value="{{ $filter['topic_id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="word" placeholder="关键词" value="{{ $filter['word'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-1">
                                            <button type="submit" class="btn btn-primary">搜索</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-body  no-padding">
                        <form name="itemForm" id="item_form" method="POST">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <tr>
                                        <th><input type="checkbox" class="checkbox-toggle" /></th>
                                        <th>标题</th>
                                        <th>公众号</th>
                                        <th>作者</th>
                                        <th>链接</th>
                                        <th>话题Id</th>
                                        <th>时间</th>
                                    </tr>
                                    @foreach($articles as $article)
                                        <tr>
                                            <td><input type="checkbox" name="id[]" value="{{ $article->_id }}"/></td>
                                            <td>{{ $article->title }}</td>
                                            <td>{{ $article->withAuthor()->name }}</td>
                                            <td>{{ $article->author }}</td>
                                            <td><a href="{{ $article->content_url }}" target="_blank">链接</a></td>
                                            <td>{{ $article->topic_id }}</td>
                                            <td>{{ timestamp_format($article->date_time) }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </form>
                    </div>
                    <div class="box-footer clearfix">
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="btn-group">
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除选中项" onclick="confirm_submit('item_form','{{  route('admin.inwehub.wechat.article.destroy') }}','确认删除选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $articles->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $articles->render()) !!}
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </section>

@endsection

@section('script')
    <script type="text/javascript">
        set_active_menu('manage_inwehub',"{{ route('admin.inwehub.wechat.article.index') }}");
    </script>
@endsection