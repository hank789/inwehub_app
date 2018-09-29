@extends('admin/public/layout')
@section('title')微信公众号管理@endsection
@section('content')
    <section class="content-header">
        <h1>
            微信公众号管理
            <small>管理微信公众号</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-4">
                                <div class="btn-group">
                                    <a href="{{ route('admin.scraper.wechat.author.create') }}" class="btn btn-default btn-sm"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="禁用选中项" onclick="confirm_submit('item_form','{{  route('admin.scraper.wechat.author.destroy') }}','确认不再抓取选中项的数据？')"><i class="fa fa-lock"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-8">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.scraper.wechat.author.index') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="word" placeholder="关键词" value="{{ $filter['word'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="wx_hao" placeholder="公众号" value="{{ $filter['wx_hao'] or '' }}"/>
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
                                    <div class="col-xs-1">
                                        <a class="btn btn-default" href="{{ route('admin.scraper.wechat.author.sync') }}" data-toggle="tooltip" title="抓取数据"><i class="fa fa-refresh"></i></a>
                                    </div>
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
                                        <th>公众号名称</th>
                                        <th>微信号</th>
                                        <th>圈子</th>
                                        <th>文章发布者</th>
                                        <th>时间</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($pending as $author)
                                        <tr>
                                            <td><input type="checkbox" name="id[]" value="{{ $author->_id }}"/></td>
                                            <td>{{ $author->_id }}</td>
                                            <td>{{ $author->name }}</td>
                                            <td>{{ $author->wx_hao }}</td>
                                            <td>{{ '' }}</td>
                                            <td>{{ '' }}</td>
                                            <td>{{ timestamp_format($author->create_at) }}</td>
                                            <td><span class="label label-danger">待抓取</span> </td>
                                            <td>

                                            </td>
                                        </tr>
                                    @endforeach
                                    @foreach($authors as $author)
                                        <tr>
                                            <td><input type="checkbox" name="id[]" value="{{ $author->_id }}"/></td>
                                            <td>{{ $author->_id }}</td>
                                            <td>{{ $author->name }}</td>
                                            <td>{{ $author->wx_hao }}</td>
                                            <td>{{ $author->group?$author->group->name:'' }}</td>
                                            <td>{{ $author->user?$author->user->name:'' }}</td>
                                            <td>{{ timestamp_format($author->create_time) }}</td>
                                            <td><span class="label @if($author->status===0) label-danger  @else label-success @endif">{{ trans_common_status($author->status) }} {{ $author->is_auto_publish?'自动发布文章':'' }}</span> </td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" href="{{ route('admin.scraper.wechat.author.edit',['id'=>$author->_id]) }}" data-toggle="tooltip" title="审核"><i class="fa fa-edit"></i></a>
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
                                    <a href="{{ route('admin.scraper.wechat.author.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="创建微信公众号"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="禁用选中项" onclick="confirm_submit('item_form','{{  route('admin.scraper.wechat.author.destroy') }}','确认不再抓取选中项的数据？')"><i class="fa fa-lock"></i></button>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $authors->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $authors->appends($filter)->render()) !!}
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
        set_active_menu('manage_scraper',"{{ route('admin.scraper.wechat.author.index') }}");
    </script>
@endsection