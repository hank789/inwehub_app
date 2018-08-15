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
                                            <div class="col-xs-2">
                                                <input type="text" class="form-control" name="word" placeholder="关键词" value="{{ $filter['word'] or '' }}"/>
                                            </div>
                                            <div class="col-xs-2">
                                                <div>
                                                    <label><input type="checkbox" name="sortByRate" value="1" @if ( $filter['sortByRate']??0) checked @endif >热度排序</label>
                                                </div>
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
                                        <th>外链</th>
                                        <th>封面图片</th>
                                        <th>热度</th>
                                        <th>类型</th>
                                        <th>浏览数</th>
                                        <th>圈子</th>
                                        <th>点赞类型</th>
                                        <th>发布者</th>
                                        <th>专栏作者</th>
                                        <th>创建时间</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($submissions as $submission)
                                        <tr id="submission_{{ $submission->id }}">
                                            <td><input type="checkbox" value="{{ $submission->id }}" name="ids[]"/></td>
                                            <td>{{ $submission->id }}</td>
                                            <td><a href="{{ config('app.mobile_url').'#/c/'.$submission->category_id.'/'.$submission->slug }}" target="_blank">{{ str_limit(strip_tags($submission->title)) }}</a></td>
                                            <td><a href="{{ $submission->type == 'link'?$submission->data['url']:'#' }}" target="_blank">外链</a></td>
                                            <td>
                                                @if ($submission->data['img'] && is_array($submission->data['img']))
                                                    @foreach($submission->data['img'] as $img)
                                                        <img width="100" height="100" src="{{ $img }}">
                                                    @endforeach
                                                @elseif ($submission->data['img'])
                                                    <img width="100" height="100" src="{{ $submission->data['img'] ??'' }}">
                                                @endif
                                            </td>
                                            <td>{{ $submission->rate }}</td>
                                            <td>{{ $submission->type }}</td>
                                            <td>{{ $submission->views }}</td>
                                            <td>{{ $submission->group->name }}</td>
                                            <td>
                                                <select onchange="setSupportType({{ $submission->id }},this)">
                                                    <option value="1" @if($submission->support_type == 1) selected @endif> 赞|踩</option>
                                                    <option value="2" @if($submission->support_type == 2) selected @endif> 看好|不看好</option>
                                                    <option value="3" @if($submission->support_type == 3) selected @endif> 支持|反对</option>
                                                    <option value="4" @if($submission->support_type == 4) selected @endif> 意外|不意外</option>
                                                </select>
                                            </td>
                                            <td>{{ $submission->owner->name }}</td>
                                            <td>
                                                @if ($submission->author_id)
                                                    <span><img style="width: 30px;height: 30px;" src="{{ $submission->author->avatar }}" class="img-flag" />{{ $submission->author->name }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $submission->created_at }}</td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" href="{{ route('admin.operate.article.edit',['id'=>$submission->id]) }}" data-toggle="tooltip" title="编辑信息"><i class="fa fa-edit"></i></a>
                                                    <a class="btn btn-default btn-sm btn-setfav" data-toggle="tooltip" title="设为精选" data-source_id = "{{ $submission->id }}"><i class="fa fa-heart"></i></a>
                                                    <a class="btn btn-default btn-sm btn-delete" data-toggle="tooltip" title="删除文章" data-source_id = "{{ $submission->id }}"><i class="fa fa-trash-o"></i></a>
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
                                <div class="col-xs-3">
                                    <div class="btn-group">
                                        <button class="btn btn-default btn-sm" data-toggle="tooltip" title="设为精选" onclick="confirm_submit('item_form','{{  route('admin.operate.article.verify_recommend') }}','确认将选中项设为精选推荐项？')"><i class="fa fa-heart"></i></button>
                                        <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除文章" onclick="confirm_submit('item_form','{{  route('admin.operate.article.destroy') }}', '确认删除选中项？')"><i class="fa fa-trash-o"></i></button>
                                    </div>
                                </div>
                                <div class="col-sm-9">
                                    <div class="text-right">
                                        <span class="total-num">共 {{ $submissions->total() }} 条数据</span>
                                        {!! str_replace('/?', '?', $submissions->appends($filter)->render()) !!}
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
        set_active_menu('operations',"{{ route('admin.operate.article.index') }}");
        function setSupportType(id,obj) {
            $.post('/admin/submission/setSupportType',{id: id, support_type: obj.value},function(msg){

            });
        }
        $(function(){
            $(".btn-delete").click(function(){
                if(!confirm('确认删除该文章？')){
                    return false;
                }
                $(this).button('loading');
                var follow_btn = $(this);
                var source_id = $(this).data('source_id');

                $.post('/admin/submission/destroy',{ids: source_id},function(msg){
                    follow_btn.removeClass('disabled');
                    follow_btn.removeAttr('disabled');
                    $("#submission_" + source_id).css('display','none');
                });
            });
            $(".btn-setfav").click(function(){
                if(!confirm('确认将该文章设为精选推荐项？')){
                    return false;
                }
                $(this).button('loading');
                var follow_btn = $(this);
                var source_id = $(this).data('source_id');

                $.post('/admin/submission/verify_recommend',{ids: [source_id]},function(msg){
                    follow_btn.html('已为精选');
                });
            });
        });
    </script>
@endsection