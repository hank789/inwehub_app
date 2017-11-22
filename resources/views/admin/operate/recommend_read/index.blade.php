@extends('admin/public/layout')

@section('title')精选推荐@endsection

@section('content')
    <section class="content-header">
        <h1>精选推荐</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-3">
                                <div class="btn-group">
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.operate.recommendRead.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="取消推荐选中项" onclick="confirm_submit('item_form','{{  route('admin.operate.recommendRead.cancel_verify') }}','确认取消推荐选中项？')"><i class="fa fa-lock"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除推荐" onclick="confirm_submit('item_form','{{  route('admin.operate.recommendRead.destroy',['id'=>0]) }}', '确认删除选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-9">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.operate.recommendRead.index') }}">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-4">
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
                                        <th><input type="checkbox" class="checkbox-toggle"/></th>
                                        <th>ID</th>
                                        <th>标题</th>
                                        <th>封面图片</th>
                                        <th>排序</th>
                                        <th>类型</th>
                                        <th>审核状态</th>
                                        <th>更新时间</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($recommendations as $item)
                                        <tr>
                                            <td><input type="checkbox" value="{{ $item->id }}" name="ids[]"/></td>
                                            <td>{{ $item->id }}</td>
                                            <td><a href="{{ $item->getWebUrl() }}" target="_blank">{{ $item->data['title'] }}</a></td>
                                            <td>
                                                @if ($item->data['img'] && is_array($item->data['img']))
                                                    @foreach($item->data['img'] as $img)
                                                        <img width="100" height="100" src="{{ $img }}">
                                                    @endforeach
                                                @elseif ($item->data['img'])
                                                    <img width="100" height="100" src="{{ $item->data['img'] ??'' }}">
                                                @endif
                                            </td>
                                            <td>{{ $item->sort }}</td>
                                            <td>{{ $item->getReadTypeName() }}</td>
                                            <td><span class="label @if($item->audit_status===0) label-danger  @else label-success @endif">{{ trans_authentication_status($item->audit_status) }}</span> </td>
                                            <td>{{ $item->updated_at }}</td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" href="{{ route('admin.operate.recommendRead.edit',['id'=>$item->id]) }}" data-toggle="tooltip" title="编辑"><i class="fa fa-edit"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </form>
                    </div>
                    <div class="box-footer clearfix">
                        {!! str_replace('/?', '?', $recommendations->render()) !!}
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('script')
    <script type="text/javascript">
        set_active_menu('operations',"{{ route('admin.operate.recommendRead.index') }}");
    </script>
@endsection