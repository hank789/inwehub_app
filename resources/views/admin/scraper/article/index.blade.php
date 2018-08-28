@extends('admin/public/layout')

@section('title')文章待处理@endsection
@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>发现分享</h1>
    </section>
    <section id="article_content" class="content">
        <div class="row">
            <div class="col-xs-12 col-lg-6 col-md-6">
                <div class="box">
                        <div class="box-header">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="row">
                                        <form name="searchForm" action="{{ route('admin.operate.article.index') }}">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <div class="col-xs-2">
                                                <input type="text" class="form-control" name="word" placeholder="关键词" value="{{ $filter['word'] or '' }}"/>
                                            </div>
                                            <div class="col-xs-2">
                                                <select class="form-control" name="status">
                                                    <option value="-1">--状态--</option>
                                                    @foreach(trans_article_status('all') as $key => $status)
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
                                        <th>操作</th>
                                        <th>点赞类型</th>
                                        <th>标题</th>
                                        <th>创建时间</th>
                                    </tr>
                                    @foreach($articles as $article)
                                        <tr id="submission_{{ $article->_id }}">
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default btn-sm btn-publish" data-toggle="tooltip" title="发布文章" data-source_id = "{{ $article->_id }}"><i class="fa fa-check-square-o"></i></a>
                                                @if (!$article->isRecommendRead())
                                                        <a class="btn btn-default btn-sm btn-setfav" id="submission_setfav_{{ $article->_id }}" data-toggle="tooltip" title="设为精选" data-source_id = "{{ $article->_id }}" data-title="{{ $article->title }}"><i class="fa fa-heart"></i></a>
                                                    @endif
                                                    <a class="btn btn-default btn-sm btn-delete" data-toggle="tooltip" title="删除文章" data-source_id = "{{ $article->_id }}"><i class="fa fa-trash-o"></i></a>
                                                </div>
                                            </td>
                                            <td>
                                                <select onchange="setSupportType({{ $article->id }},this)">
                                                    <option value="1" @if($article->topic_id ? $article->submission()->support_type == 1 : true) selected @endif> 赞|踩</option>
                                                    <option value="2" @if($article->topic_id ? $article->submission()->support_type == 2 : false) selected @endif> 看好|不看好</option>
                                                    <option value="3" @if($article->topic_id ? $article->submission()->support_type == 3 : false) selected @endif> 支持|反对</option>
                                                    <option value="4" @if($article->topic_id ? $article->submission()->support_type == 4 : false) selected @endif> 意外|不意外</option>
                                                </select>
                                            </td>
                                            <td><a class="btn-viewinfo" href="javascript:void(0)" data-url="{{ $article->content_url }}" data-title="{{ $article->title }}" data-description="{{ $article->description }}" data-body="{{ $article->body }}">{{ str_limit(strip_tags($article->title)) }}</a></td>
                                            <td>{{ $article->date_time }}</td>
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
                                        <span class="total-num">共 {{ $articles->total() }} 条数据</span>
                                        {!! str_replace('/?', '?', $articles->appends($filter)->render()) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div data-spy="affix" style="overflow-y:auto">
                    <h2 id="article_title"></h2>
                    <div id="article_description"></div>
                    <div id="article_body"></div>
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
    <style>
        .iframe-div {
            position: fixed;
            top: 50px;
            width: 50%;
            z-index:1040;
        }
    </style>
@endsection

@section('script')
    <script src="{{ asset('/static/js/select2/js/select2.min.js')}}"></script>
    <script type="text/javascript">
        set_active_menu('operations',"{{ route('admin.scraper.article.index') }}");
        function setSupportType(id,obj) {
            $.post('/admin/submission/setSupportType',{id: id, support_type: obj.value},function(msg){

            });
        }
        $(function(){
            $("#select_tags_id").select2({
                theme:'bootstrap',
                placeholder: "标签"
            });

            $(".btn-viewinfo").click(function(){
                var title = $(this).data('title');
                var description = $(this).data('description');
                var body = $(this).data('body');
                var url = $(this).data('url');

                $("#article_title").html("<a target='_blank' href='"+url+"'>" + title + "</a>");
                $("#article_description").html(description);
                $("#article_body").html(body);
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