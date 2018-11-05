@extends('admin/public/layout')

@section('title')产品点评@endsection
@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>产品点评</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                        <div class="box-header">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="row">
                                        <form name="searchForm" action="{{ route('admin.review.submission.index') }}">
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
                                        <th>ID</th>
                                        <th>标题</th>
                                        <th>评分</th>
                                        <th>热度</th>
                                        <th>标签</th>
                                        <th>浏览数</th>
                                        <th>匿名</th>
                                        <th>发布者</th>
                                        <th>状态</th>
                                    </tr>
                                    @foreach($submissions as $submission)
                                        <tr id="submission_{{ $submission->id }}">
                                            <td>{{ $submission->id }}</td>
                                            <td>
                                                <a href="{{ config('app.mobile_url').'#/c/'.$submission->category_id.'/'.$submission->slug }}" target="_blank">{{ str_limit(strip_tags($submission->title)) }}</a>
                                                <br>{{ $submission->created_at }}
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" target="_blank" href="{{ $submission->type == 'link'?$submission->data['url']:'#' }}" data-toggle="tooltip" title="原始地址"><i class="fa fa-eye"></i></a>
                                                    <a class="btn btn-default" href="{{ route('admin.review.submission.edit',['id'=>$submission->id]) }}" data-toggle="tooltip" title="编辑信息"><i class="fa fa-edit"></i></a>
                                                    @if (!$submission->isRecommendRead() && false)
                                                        <a class="btn btn-default btn-sm btn-setfav" id="submission_setfav_{{ $submission->id }}" data-toggle="tooltip" title="设为精选" data-source_id = "{{ $submission->id }}" data-title="{{ $submission->title }}"><i class="fa fa-heart"></i></a>
                                                    @endif
                                                    <a class="btn btn-default btn-sm btn-setveriy" data-toggle="tooltip" title="{{ $submission->status ? '设为待审核':'审核成功' }}" data-title="{{ $submission->status ? '设为待审核':'审核成功' }}" data-source_id = "{{ $submission->id }}"><i class="fa {{ $submission->status ? 'fa-lock':'fa-check-square-o' }}"></i></a>
                                                    <a class="btn btn-default btn-sm btn-setgood" data-toggle="tooltip" title="{{ $submission->is_recommend ? '取消优质':'设为优质' }}" data-title="{{ $submission->is_recommend ? '取消优质':'设为优质' }}" data-source_id = "{{ $submission->id }}"><i class="fa {{ $submission->is_recommend ? 'fa-thumbs-down':'fa-thumbs-up' }}"></i></a>
                                                @if ($submission->status == 0)
                                                        <a class="btn btn-default btn-sm btn-delete" data-toggle="tooltip" title="删除文章" data-source_id = "{{ $submission->id }}"><i class="fa fa-trash-o"></i></a>
                                                    @endif
                                                    <select onchange="setSupportType({{ $submission->id }},this)">
                                                        <option value="1" @if($submission->support_type == 1) selected @endif> 赞|踩</option>
                                                        <option value="2" @if($submission->support_type == 2) selected @endif> 看好|不看好</option>
                                                        <option value="3" @if($submission->support_type == 3) selected @endif> 支持|反对</option>
                                                        <option value="4" @if($submission->support_type == 4) selected @endif> 意外|不意外</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                {{ $submission->rate_star }}
                                            </td>
                                            <td>{{ $submission->rate }}</td>
                                            <td>
                                                @foreach($submission->tags as $tagInfo)
                                                    {{ $tagInfo->name.',' }}
                                                @endforeach
                                            </td>
                                            <td>{{ $submission->views }}</td>
                                            <td> {{ $submission->hide ? '匿名':'公开' }}</td>
                                            <td>{{ $submission->owner->name }}</td>
                                            <td><span class="label @if($submission->status===0) label-warning  @else label-success @endif">{{ trans_common_status($submission->status) }}</span> </td>
                                        </tr>
                                    @endforeach
                                </table>
                                </div>
                            </form>
                        </div>
                        <div class="box-footer clearfix">
                            <div class="row">
                                <div class="col-sm-12">
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
        <div class="modal fade" id="set_fav_modal" tabindex="-1"  role="dialog" aria-labelledby="set_fav_modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="exampleModalLabel">设为精选</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="tagIds" id="tagIds" />
                        <input type="hidden" name="id" id="id" />
                        <div class="box-body">
                            <div class="form-group">
                                <label>标题:</label>
                                <input type="text" name="title" id="title" class="form-control"  placeholder="标题" value="">
                            </div>
                            <div class="form-group">
                                <label>标签语</label>
                                <input type="text" name="tips" id="tips" class="form-control"  placeholder="标签语" value="">
                            </div>
                            <div class="form-group">
                                <label for="select_tags_id" class="control-label">标签:</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select style="width: auto" id="select_tags_id" name="select_tags_id" class="form-control" multiple="multiple" >
                                            @foreach($tags as $tag)
                                                <option value="{{ $tag['id'] }}">{{ $tag['text'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-primary" id="set_fav_submit">确认</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('script')
    <script src="{{ asset('/static/js/select2/js/select2.min.js')}}"></script>
    <script type="text/javascript">
        set_active_menu('manage_review',"{{ route('admin.review.submission.index') }}");
        function setSupportType(id,obj) {
            $.post('/admin/submission/setSupportType',{id: id, support_type: obj.value},function(msg){

            });
        }
        $(function(){
            $("#select_tags_id").select2({
                theme:'bootstrap',
                placeholder: "标签"
            });

            $("#select_tags_id").change(function(){
                $("#tagIds").val($("#select_tags_id").val());
            });
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
            $(".btn-setgood").click(function(){
                var title = $(this).data('title');
                if(!confirm('确认' + title + '？')){
                    return false;
                }
                $(this).button('loading');
                var follow_btn = $(this);
                var source_id = $(this).data('source_id');

                $.post('/admin/submission/setgood',{id: source_id},function(msg){
                    follow_btn.removeClass('disabled');
                    follow_btn.removeAttr('disabled');
                    if(msg == 'failed') {
                        follow_btn.html('<i class="fa fa-thumbs-down"></i>');
                        follow_btn.data('title','取消优质');
                    } else {
                        follow_btn.html('<i class="fa fa-thumbs-up"></i>');
                        follow_btn.data('title','设为优质');
                    }
                });
            });

            $(".btn-setveriy").click(function(){
                var title = $(this).data('title');
                if(!confirm('确认' + title + '？')){
                    return false;
                }
                $(this).button('loading');
                var follow_btn = $(this);
                var source_id = $(this).data('source_id');

                $.post('/admin/submission/setveriy',{id: source_id},function(msg){
                    follow_btn.removeClass('disabled');
                    follow_btn.removeAttr('disabled');
                    if(msg == 'failed') {
                        follow_btn.html('<i class="fa fa-lock"></i>');
                        follow_btn.data('title','设为待审核');
                    } else {
                        follow_btn.html('<i class="fa fa-check-square-o"></i>');
                        follow_btn.data('title','审核成功');
                    }
                });
            });

            $(".btn-setfav").click(function(){
                var source_id = $(this).data('source_id');
                $("#id").val(source_id);
                $("#title").val($(this).data('title'));
                $('#set_fav_modal').modal('show');
            });
            $("#set_fav_submit").click(function(){
                var id = $("#id").val();
                $.post('/admin/submission/verify_recommend',{id: id,title: $("#title").val(),tagIds: $("#tagIds").val(),tips: $("#tips").val()},function(msg){

                });
                $('#submission_setfav_' + id).css('display','none');
                $('#set_fav_modal').modal('hide');
            });
        });
    </script>
@endsection