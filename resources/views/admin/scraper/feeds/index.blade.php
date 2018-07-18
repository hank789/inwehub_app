@extends('admin/public/layout')
@section('title')数据源管理@endsection
@section('content')
    <section class="content-header">
        <h1>
            Inwehub数据源管理
            <small>管理Inwehub的数据源</small>
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
                                    <a href="{{ route('admin.scraper.feeds.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="创建数据源"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.scraper.feeds.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="禁用选中项" onclick="confirm_submit('item_form','{{  route('admin.scraper.feeds.destroy') }}','确认禁用选中项？')"><i class="fa fa-lock"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-10">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.scraper.feeds.index') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
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
                                        <th>站点</th>
                                        <th>圈子</th>
                                        <th>源地址</th>
                                        <th>时间</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($feeds as $article)
                                        <tr>
                                            <td><input type="checkbox" name="id[]" value="{{ $article->id }}"/></td>
                                            <td><a href="{{ route('admin.scraper.feeds.edit',['id'=>$article->id]) }}" target="_blank">{{ $article->name }}</a></td>
                                            <td>{{ $article->group?$article->group->name:'' }}</td>
                                            <td>{{ $article->source_link }}</td>
                                            <td>{{ timestamp_format($article->created_at) }}</td>
                                            <td><span class="label @if($article->status===0) label-danger  @else label-success @endif">{{ trans_common_status($article->status) }}</span> </td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" href="{{ route('admin.scraper.feeds.edit',['id'=>$article->id]) }}" data-toggle="tooltip" title="编辑"><i class="fa fa-edit"></i></a>
                                                    <a class="btn btn-default" href="{{ route('admin.scraper.feeds.sync',['id'=>$article->id]) }}" data-toggle="tooltip" title="抓取数据"><i class="fa fa-refresh"></i></a>

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
                                    <a href="{{ route('admin.scraper.feeds.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="创建数据源"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.scraper.feeds.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="禁用选中项" onclick="confirm_submit('item_form','{{  route('admin.scraper.feeds.destroy') }}','确认禁用选中项？')"><i class="fa fa-lock"></i></button>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $feeds->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $feeds->render()) !!}
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
        set_active_menu('manage_scraper',"{{ route('admin.scraper.feeds.index') }}");
    </script>
@endsection