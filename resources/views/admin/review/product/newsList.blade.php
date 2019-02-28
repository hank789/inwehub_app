@extends('admin/public/layout')
@section('title')产品资讯管理@endsection
@section('content')
    <section class="content-header">
        <h1>
            产品资讯管理-{{$tag->name}}
            <small>管理所有产品相关文章</small>
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
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.scraper.wechat.article.verify') }}','确认发布选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="禁用选中项" onclick="confirm_submit('item_form','{{  route('admin.scraper.wechat.article.destroy') }}','确认禁用选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-10">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.review.product.newsList',['tag_id'=>$tag->id]) }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="mp_id" placeholder="公众号ID" value="{{ $filter['mp_id'] or '' }}"/>
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
                                        <th>ID</th>
                                        <th>标题</th>
                                        <th>公众号</th>
                                        <th>时间</th>
                                        <th>状态</th>
                                    </tr>
                                    @foreach($articles as $article)
                                        <tr>
                                            <td><input type="checkbox" name="id[]" value="{{ $article->_id }}"/></td>
                                            <td>{{ $article->_id }}</td>
                                            <td><a href="{{ $article->content_url }}" target="_blank">{{ $article->title }}</a></td>
                                            <td>{{ $article->withAuthor()->name }}</td>
                                            <td>{{ timestamp_format($article->date_time) }}</td>
                                            <td><span class="label @if($article->status===3) label-danger @elseif ($article->status===1) label-warning @else label-success @endif">{{ trans_article_status($article->status) }}</span> </td>
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
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.scraper.wechat.article.verify') }}','确认发布选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="禁用选中项" onclick="confirm_submit('item_form','{{  route('admin.scraper.wechat.article.destroy') }}','确认禁用选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $articles->count() }} 条数据</span>
                                    {!! str_replace('/?', '?', $articles->appends($filter)->render()) !!}
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
        set_active_menu('manage_review',"{{ route('admin.review.product.index') }}");
    </script>
@endsection