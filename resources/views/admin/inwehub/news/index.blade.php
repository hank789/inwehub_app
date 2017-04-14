@extends('admin/public/layout')
@section('title')新闻管理@endsection
@section('content')
    <section class="content-header">
        <h1>
            Inwehub新闻管理
            <small>管理Inwehub的所有新闻</small>
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
                                    <a href="{{ route('admin.inwehub.news.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="创建新新闻"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除选中项" onclick="confirm_submit('item_form','{{  route('admin.inwehub.news.destroy') }}','确认删除选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-10">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.inwehub.news.index') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-3">
                                            <input type="text" name="date_range" id="date_range" class="form-control" placeholder="时间范围" value="{{ $filter['date_range'] or '' }}" />
                                        </div>
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="user_id" placeholder="作者UID" value="{{ $filter['user_id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="topic_id" placeholder="话题ID" value="{{ $filter['topic_id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="word" placeholder="关键词" value="{{ $filter['word'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <select class="form-control" name="status">
                                                <option value="-1">--状态--</option>
                                                @foreach(trans_common_status('all') as $key => $status)
                                                    <option value="{{ $key }}" @if( isset($filter['status']) && $filter['status']==$key) selected @endif >{{ $status }}</option>
                                                @endforeach
                                            </select>
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
                                        <th>ID</th>
                                        <th>作者</th>
                                        <th>站点</th>
                                        <th>链接</th>
                                        <th>话题Id</th>
                                        <th>时间</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($news as $article)
                                        <tr>
                                            <td><input type="checkbox" name="id[]" value="{{ $article->id }}"/></td>
                                            <td>{{ $article->id }}</td>
                                            <td><a href="{{ route('admin.inwehub.news.edit',['id'=>$article->id]) }}" target="_blank">{{ $article->title }}</a></td>
                                            <td>{{ $article->author_name }}</td>
                                            <td>{{ $article->site_name }}</td>
                                            <td><a href="{{ $article->url }}" target="_blank">链接</a></td>
                                            <td>{{ $article->topic_id }}</td>
                                            <td>{{ timestamp_format($article->publish_date) }}</td>
                                            <td><span class="label @if($article->status===0) label-danger  @else label-success @endif">{{ trans_common_status($article->status) }}</span> </td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" href="{{ route('admin.inwehub.news.edit',['id'=>$article->id]) }}" data-toggle="tooltip" title="编辑"><i class="fa fa-edit"></i></a>
                                                </div>
                                            </td>
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
                                    <a href="{{ route('admin.inwehub.news.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="创建新新闻"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除选中项" onclick="confirm_submit('item_form','{{  route('admin.inwehub.news.destroy') }}','确认删除选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $news->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $news->render()) !!}
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
        set_active_menu('manage_inwehub',"{{ route('admin.inwehub.news.index') }}");
    </script>
@endsection