@extends('admin/public/layout')

@section('title')发现分享@endsection
@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>发现分享<small>领域：{{ implode('，',array_column($tags,'text')) }}</small></h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                        <div class="box-header">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="row">
                                        <form name="searchForm" action="{{ route('admin.operate.article.index') }}">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <div class="col-xs-2">
                                                <input type="text" class="form-control" name="id" placeholder="文章id" value="{{ $filter['id'] or '' }}"/>
                                            </div>
                                            <div class="col-xs-2">
                                                <input type="text" class="form-control" name="user_id" placeholder="UID" value="{{ $filter['user_id'] or '' }}"/>
                                            </div>
                                            <div class="col-xs-2">
                                                <input type="text" class="form-control" name="word" placeholder="关键词" value="{{ $filter['word'] or '' }}"/>
                                            </div>
                                            <div class="col-xs-2">
                                                <input type="text" class="form-control" name="group_id" placeholder="圈子ID" value="{{ $filter['group_id'] or '' }}"/>
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
                                        <th>封面图片</th>
                                        <th>热度</th>
                                        <th>标签</th>
                                        <th>类型</th>
                                        <th>浏览数</th>
                                        <th>圈子</th>
                                        <th>发布者</th>
                                    </tr>
                                    @php
                                        $pageTags = []
                                    @endphp
                                    @foreach($submissions as $submission)
                                        <tr id="submission_{{ $submission->id }}">
                                            <td>{{ $submission->id }}</td>
                                            <td>
                                                <a href="{{ config('app.mobile_url').'#/c/'.$submission->category_id.'/'.$submission->slug }}" target="_blank">{{ str_limit(strip_tags($submission->title?:$submission->data['title'])) }}</a>
                                                <br>{{ $submission->created_at }}
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" target="_blank" href="{{ $submission->type == 'link'?$submission->data['url']:'#' }}" data-toggle="tooltip" title="原始地址"><i class="fa fa-eye"></i></a>
                                                    <a class="btn btn-default" href="{{ route('admin.operate.article.edit',['id'=>$submission->id]) }}" data-toggle="tooltip" title="编辑信息"><i class="fa fa-edit"></i></a>
                                                    @if (!$submission->isRecommendRead())
                                                        <a class="btn btn-default btn-sm btn-setfav" id="submission_setfav_{{ $submission->id }}" data-toggle="tooltip" title="设为精选" data-source_id = "{{ $submission->id }}" data-title="{{ $submission->type == 'link'?$submission->data['title']:$submission->title }}"><i class="fa fa-heart"></i></a>
                                                    @endif
                                                    <a class="btn btn-default btn-sm btn-setgood" data-toggle="tooltip" title="{{ $submission->is_recommend ? '取消优质':'设为优质' }}" data-title="{{ $submission->is_recommend ? '取消优质':'设为优质' }}" data-source_id = "{{ $submission->id }}"><i class="fa {{ $submission->is_recommend ? 'fa-thumbs-down':'fa-thumbs-up' }}"></i></a>
                                                    <a class="btn btn-default btn-sm btn-delete" data-toggle="tooltip" title="删除文章" data-source_id = "{{ $submission->id }}"><i class="fa fa-trash-o"></i></a>
                                                    <select onchange="setSupportType({{ $submission->id }},this)">
                                                        <option value="1" @if($submission->support_type == 1) selected @endif> 赞|踩</option>
                                                        <option value="2" @if($submission->support_type == 2) selected @endif> 看好|不看好</option>
                                                        <option value="3" @if($submission->support_type == 3) selected @endif> 支持|反对</option>
                                                        <option value="4" @if($submission->support_type == 4) selected @endif> 意外|不意外</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                @if (isset($submission->data['img']) && is_array($submission->data['img']))
                                                    @foreach($submission->data['img'] as $img)
                                                        <img width="100" height="100" src="{{ $img }}">
                                                    @endforeach
                                                @elseif (isset($submission->data['img']))
                                                    <img width="100" height="100" src="{{ $submission->data['img'] ??'' }}">
                                                @endif
                                            </td>
                                            <td>{{ $submission->rate }}</td>
                                            <td>
                                                @php
                                                    $pageTags += $submission->tags->pluck('name','id')->toArray()
                                                @endphp
                                                @foreach($submission->tags as $tagInfo)
                                                    {{ $tagInfo->name.',' }}
                                                @endforeach
                                                <a class="btn-edit_tags" data-source_id = "{{ $submission->id }}" data-title="{{ $submission->title }}" data-tags="{{ implode(',',$submission->tags->pluck('id')->toArray()) }}" data-toggle="tooltip" title="修改标签"><i class="fa fa-edit"></i></a>
                                            </td>
                                            <td>{{ $submission->type }}</td>
                                            <td>{{ $submission->views }}</td>
                                            <td>{{ $submission->group_id ? $submission->group->name:'' }}</td>
                                            <td>{{ $submission->owner->name }}</td>
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

        <div class="modal fade" id="set_tags_modal" tabindex="-1"  role="dialog" aria-labelledby="set_tags_modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="exampleModalLabel">修改标签-<span id="title2"></span></h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="tagIds2" id="tagIds2" />
                        <input type="hidden" name="id2" id="id2" />
                        <div class="box-body">
                            <div class="form-group">
                                <label for="select_tags_id2" class="control-label">标签:</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select style="width: auto" id="select_tags_id2" name="select_tags_id2" class="form-control" multiple="multiple" >
                                            @foreach($pageTags as $key=>$name)
                                                <option value="{{ $key }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-primary" id="set_tags_submit">确认</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('script')
    <script src="{{ asset('/static/js/select2/js/select2.min.js')}}"></script>
    <script type="text/javascript">
        set_active_menu('operations',"{{ route('admin.operate.article.index') }}");

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

            $("#select_tags_id2").select2({
                theme:'bootstrap',
                placeholder: "标签",
                ajax: {
                    url: '/manager/ajax/loadTags',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            word: params.term,
                            type: 'allC'
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                minimumInputLength:2,
                tags:false
            });

            $("#select_tags_id2").change(function(){
                $("#tagIds2").val($("#select_tags_id2").val());
            });

            $(".btn-edit_tags").click(function(){
                var source_id = $(this).data('source_id');
                var cs = $(this).data('tags');
                $("#id2").val(source_id);
                $("#title2").html($(this).data('title'));
                $("#select_tags_id2").val(cs.toString().split(','));
                $('#select_tags_id2').trigger('change');
                $('#set_tags_modal').modal('show');
            });
            $("#set_tags_submit").click(function(){
                var id = $("#id2").val();
                $.post('/admin/submission/changeTags',{id: id,tagIds: $("#tagIds2").val()},function(msg){
                    window.location.reload()
                });
                $('#set_tags_modal').modal('hide');
            });
        });
    </script>
@endsection