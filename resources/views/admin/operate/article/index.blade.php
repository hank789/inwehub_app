@extends('admin/public/layout')

@section('title')发现分享@endsection

@section('content')
    <section class="content-header">
        <h1>发现分享</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                        <div class="box-header">
                            <div class="row">
                                <div class="col-xs-3">
                                    <div class="btn-group">
                                        <button class="btn btn-default btn-sm" data-toggle="tooltip" title="设为精选" onclick="confirm_submit('item_form','{{  route('admin.operate.article.verify_recommend') }}','确认将选中项设为精选推荐项？')"><i class="fa fa-heart"></i></button>
                                        <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除文章" onclick="confirm_submit('item_form','{{  route('admin.operate.article.destroy') }}', '确认删除选中项？')"><i class="fa fa-trash-o"></i></button>
                                    </div>
                                </div>
                                <div class="col-xs-9">
                                    <div class="row">
                                        <form name="searchForm" action="{{ route('admin.operate.article.index') }}">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <div class="col-xs-2">
                                                <input type="text" class="form-control" name="user_id" placeholder="UID" value="{{ $filter['user_id'] or '' }}"/>
                                            </div>
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
                                        <th>频道</th>
                                        <th>类型</th>
                                        <th>发布者</th>
                                        <th>更新时间</th>
                                    </tr>
                                    @foreach($submissions as $submission)
                                        <tr>
                                            <td><input type="checkbox" value="{{ $submission->id }}" name="ids[]"/></td>
                                            <td>{{ $submission->id }}</td>
                                            <td><a href="{{ config('app.mobile_url').'#/c/'.$submission->category_id.'/'.$submission->slug }}" target="_blank">{{ strip_tags($submission->title) }}</a></td>
                                            <td>{{ $submission->category_name }}</td>
                                            <td>{{ $submission->type }}</td>
                                            <td>{{ $submission->owner->name }}</td>
                                            <td>{{ $submission->updated_at }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                                </div>
                            </form>
                        </div>
                        <div class="box-footer clearfix">
                            {!! str_replace('/?', '?', $submissions->appends($filter)->render()) !!}
                        </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('script')
    <script type="text/javascript">
        set_active_menu('operations',"{{ route('admin.operate.article.index') }}");
    </script>
@endsection