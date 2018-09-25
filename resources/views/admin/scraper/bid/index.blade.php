@extends('admin/public/layout')

@section('title')招标信息@endsection
@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>招标信息</h1>
    </section>
    <section id="article_content" class="content">
        <div class="row">
            <div class="col-xs-12 col-lg-4 col-md-4">
                <div class="box">
                        <div class="box-header">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="row">
                                        <form name="searchForm" action="{{ route('admin.scraper.bid.index') }}">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <div class="col-xs-4">
                                                <input type="text" class="form-control" name="word" placeholder="关键词" value="{{ $filter['word'] or '' }}"/>
                                            </div>
                                            <div class="col-xs-4">
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
                                        <th>标题</th>
                                    </tr>
                                    @foreach($articles as $article)
                                        <tr id="submission_{{ $article->id }}">
                                            <td style="white-space: normal;">
                                                <a class="btn-viewinfo" href="javascript:void(0)" data-id="{{ $article->id }}" data-url="{{ $article->source_url }}" data-title="{{ $article->title }}" data-description="{{ $article->projectname }}" data-body="{{ $article->detail['bid_html_body'] }}">{{ str_limit(strip_tags($article->title)) }}</a>
                                                <br>圈子：{{ isset($article->detail['group_ids'])?$article->getGroup()->name:'' }}
                                                <br>发布时间：{{ $article->publishtime }},截止时间：{{ $article->bidopentime }}
                                                <br>发布者：{{ $article->buyer }},地区：{{ $article->area }},行业：{{ $article->industry }}
                                                <br>{{ $article->subtype }},预算：{{ $article->budget }},中标金额：{{ $article->bidamount }}
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default btn-sm" data-toggle="tooltip" title="查看文章" href="javascript:void(0)" onclick="openUrl({{ $article->id }}, '{{ $article->source_url }}')" target="_blank"><i class="fa fa-eye"></i></a>
                                                    @if ($article->topic_id <= 0)
                                                        <a class="btn btn-default btn-sm btn-publish" data-toggle="tooltip" id="submission_publish_{{ $article->id }}" title="发布文章" data-source_id = "{{ $article->id }}"><i class="fa fa-check-square-o"></i></a>
                                                    @endif
                                                    @if ($article->topic_id <= 0 && $article->status==1)
                                                        <a class="btn btn-default btn-sm btn-delete" data-toggle="tooltip" title="删除文章" data-source_id = "{{ $article->id }}"><i class="fa fa-trash-o"></i></a>
                                                    @endif
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
                                        <a href="javascript:void(0)" onclick="deleteRead()" class="btn btn-danger btn-sm" data-toggle="tooltip" title="删除已读">删除已读</a>
                                    </div>
                                </div>
                                <div class="col-sm-9">
                                    <div class="text-right">
                                        <span class="total-num">共 {{ $articles->total() }} 条数据</span>
                                        {!! str_replace('/?', '?', $articles->appends($filter)->render()) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
            <div class="col-lg-8 col-md-8" id="article_html" style="display:none;">
                <div id="article_body_html" data-spy="affix" class="row pre-scrollable" style="min-height: 500px;margin-top: -70px;" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="btn btn-default" onclick="closeModal()">Close</button><h4 class="modal-title" id="article_title"></h4>
                            </div>
                            <div class="modal-body">
                                <div id="article_description"></div>
                                <div id="article_body"></div>
                            </div>
                            <div class="modal-footer" style="text-align: left;">
                                <div class="btn-group-md" >
                                    <button type="button" class="btn btn-default" onclick="closeModal()">Close</button>
                                    <a class="btn btn-default btn-sm btn-publish" id="article_btn_publish" data-toggle="tooltip" title="发布文章" data-source_id = "0" data-button_type="1"><i class="fa fa-check-square-o"></i></a>
                                    <a class="btn btn-default btn-sm btn-delete" id="article_btn_delete" data-toggle="tooltip" title="删除文章" data-source_id = "0"><i class="fa fa-trash-o"></i></a>
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
    <style>
        #article_html img {
            margin-left:auto;
            margin-right:auto;
            max-width: 500px;
            display:block;
        }
    </style>
@endsection

@section('script')
    <script src="{{ asset('/static/js/select2/js/select2.min.js')}}"></script>
    <script type="text/javascript">
        set_active_menu('operations',"{{ route('admin.scraper.bid.index') }}");
        var readArticle = [];
        var publishArticle = [];
        function setSupportType(id,obj) {
            $.post('/admin/scraper/bid/setSupportType',{id: id, support_type: obj.value},function(msg){

            });
        }
        function openUrl(id, url) {
            $("#submission_" + id).css('background-color','#ecf0f5');
            window.open(url);
        }
        function closeModal() {
            $('#article_html').css('display','none');
        }
        function deleteRead() {
            if(!confirm('确认删除已读文章？')){
                return false;
            }
            $.post('/admin/scraper/bid/destroy',{ids: readArticle, ignoreIds: publishArticle},function(msg){
                readArticle.forEach(function (item, index) {
                    $("#submission_" + item).css('display','none');
                });
                readArticle = [];
            });
        }
        $(function(){
            $("#select_tags_id").select2({
                theme:'bootstrap',
                placeholder: "标签"
            });

            $(".btn-viewinfo").click(function(){
                $('#article_html').css('display','block');
                if(/Android|webOS|iPhone|iPod|BlackBerry/i.test(navigator.userAgent)) {
                    $('#article_body_html').css('max-width', window.screen.width + 'px');
                } else {
                    $('#article_body_html').css('max-width','1024px');
                }
                var title = $(this).data('title');
                var description = $(this).data('description');
                var body = $(this).data('body');
                var url = $(this).data('url');
                var id = $(this).data('id');
                $("#submission_" + id).css('background-color','#ecf0f5');
                readArticle.push(id);
                console.log(readArticle);

                $("#article_title").html("<a target='_blank' href='"+url+"'>" + title + "</a>");
                $("#article_description").html(description);
                $("#article_body").html(body);

                $("#article_btn_setfav").data('source_id', id);
                $("#article_btn_setfav").data('title', title);

                $("#article_btn_publish").data('source_id', id);
                $("#article_btn_delete").data('source_id', id);
                $("#article_body_html").scrollTop(10);
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

                $.ajax({
                    type: "post",
                    data: {ids: [source_id]},
                    url:"/admin/scraper/bid/destroy",
                    success: function(data){
                        if(data.code > 0){
                            alert(data.message);
                            return false;
                        }
                        follow_btn.removeClass('disabled');
                        follow_btn.removeAttr('disabled');
                        $("#submission_" + source_id).css('display','none');
                    },
                    error: function(data){
                        console.log(data);
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
                $.post('/admin/scraper/bid/verify_recommend',{id: id,title: $("#title").val(),tagIds: $("#tagIds").val(),tips: $("#tips").val()},function(msg){
                    publishArticle.push(id);
                    console.log(publishArticle);
                });
                $('#submission_setfav_' + id).css('display','none');
                $('#set_fav_modal').modal('hide');
                $("#submission_" + id).css('display','none');
            });
            $(".btn-publish").click(function(){
                $(this).button('loading');
                var follow_btn = $(this);
                var source_id = $(this).data('source_id');
                var button_type = $(this).data('button_type');
                $.post('/admin/scraper/bid/publish',{ids: [source_id]},function(msg){
                    publishArticle.push(source_id);
                    if (button_type) {
                        $("#submission_" + source_id).css('display','none');
                        follow_btn.html('<i class="fa fa-check-square-o"></i>');
                        follow_btn.button('已发布');
                    } else {
                        follow_btn.html('已发布');
                    }
                    console.log(publishArticle);
                });
            });
        });
    </script>
@endsection