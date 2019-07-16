@extends('admin/public/layout')
@section('title')微信公众号管理@endsection
@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection
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
                            <div class="col-xs-2">
                                <div class="btn-group">
                                    <a href="{{ route('admin.scraper.wechat.author.create') }}" class="btn btn-default btn-sm"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="禁用选中项" onclick="confirm_submit('item_form','{{  route('admin.scraper.wechat.author.destroy') }}','确认不再抓取选中项的数据？')"><i class="fa fa-lock"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-10">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.scraper.wechat.author.index') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="group_id" id="group_id" value="" />
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="word" placeholder="关键词" value="{{ $filter['word'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="wx_hao" placeholder="公众号" value="{{ $filter['wx_hao'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="user_id" placeholder="发布者ID" value="{{ $filter['user_id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <select id="select_group_id" name="select_group_id">
                                                <option value="-1" {{ $filter['group_id'] == -1 ? '':'selected' }}>选择圈子</option>
                                                @foreach($groups as $group)
                                                    <option value="{{ $group['id'] }}" {{ $filter['group_id'] == $group['id'] ? 'selected':'' }}>{{ $group['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-xs-2">
                                            <select name="select_region" class="form-control">
                                                <option value="-1" {{ $filter['select_region'] == -1 ? '':'selected' }}>选择领域</option>
                                                @foreach($regions as $region)
                                                    <option value="{{ $region['id'] }}" {{ $filter['select_region'] == $region['id'] ? 'selected':'' }}>{{ $region['text'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-xs-2">
                                            <select class="form-control" name="status">
                                                <option value="-2" {{ isset($filter['status']) && $filter['status']== -2 ? 'selected':'' }}>--状态--</option>
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
                                        <th>发布者</th>
                                        <th>发布领域</th>
                                        <th>最后抓取时间</th>
                                        <th>今日抓取文章数</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($pending as $author)
                                        <tr>
                                            <td><input type="checkbox" name="id[]" value="{{ $author->_id }}"/></td>
                                            <td>{{ $author->_id }}</td>
                                            <td>{{ $author->name }}</td>
                                            <td>{{ $author->wx_hao }}</td>
                                            <td></td>
                                            <td>{{ '' }}</td>
                                            <td>{{ '' }}</td>
                                            <td>{{ $author->update_time }}</td>
                                            <td>0</td>
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
                                            <td>{{ $author->user_id?$author->user->name.'['.$author->user_id.']':'智能小哈[504]' }}</td>
                                            <td>{{ implode(',',$author->tags->pluck('name')->toArray()) }}</td>
                                            <td>{{ $author->update_time }}</td>
                                            <td>{{ $author->countTodayArticle() }}</td>
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
    <script src="{{ asset('/static/js/select2/js/select2.min.js')}}"></script>
    <script type="text/javascript">
        set_active_menu('manage_scraper',"{{ route('admin.scraper.wechat.author.index') }}");
        $("#select_group_id").select2({
            theme:'bootstrap',
            placeholder: "选择圈子",
            tags:false
        });

        $("#select_group_id").change(function(){
            $("#group_id").val($("#select_group_id").val());
        });
    </script>
@endsection