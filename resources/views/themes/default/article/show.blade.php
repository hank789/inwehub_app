@extends('theme::layout.public')

@section('seo_title'){{ parse_seo_template('seo_article_title',$article) }}@endsection
@section('seo_keyword'){{ parse_seo_template('seo_article_keyword',$article) }}@endsection
@section('seo_description'){{ parse_seo_template('seo_article_description',$article) }}@endsection

@section('content')
    <div class="row mt-10">
        <div class="col-xs-12 col-md-9 main">
            <div class="widget-question widget-article">
                <h3 class="title">{{ $article->title }}</h3>
                @if($article->tags)
                    <ul class="taglist-inline">
                        @foreach($article->tags as $tag)
                            <li class="tagPopup"><a class="tag" href="{{ route('ask.tag.index',['id'=>$tag->id]) }}">{{ $tag->name }}</a></li>
                        @endforeach
                    </ul>
                @endif
                <div class="content mt-10">
                    <div class="quote mb-20">
                         {{ $article->summary }}
                    </div>
                    <div class="text-fmt">
                        {!! $article->content !!}
                    </div>
                    <div class="post-opt mt-30">
                        <ul class="list-inline text-muted">
                            <li>
                                <i class="fa fa-clock-o"></i>
                                发布于 {{ timestamp_format($article->created_at) }}
                            </li>
                            <li>阅读 ( {{$article->views}} )</li>
                            @if($article->category)
                            <li>分类：<a href="{{ route('website.blog',['category_slug'=>$article->category->slug]) }}" target="_blank">{{ $article->category->name }}</a>
                            @endif
                            </li>
                            @if($article->status !== 2 && Auth()->check() && (Auth()->user()->id === $article->user_id || Auth()->user()->isRole('admin') ) )
                            <li><a href="{{ route('blog.article.edit',['id'=>$article->id]) }}" class="edit" data-toggle="tooltip" data-placement="right" title="" data-original-title="进一步完善活动内容"><i class="fa fa-edit"></i> 编辑</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="text-center mt-10 mb-20">

                    <button id="support-button" class="btn btn-success btn-lg mr-5" data-source_id="{{ $article->id }}" data-source_type="article"  data-support_num="{{ $article->supports }}">报名人数：{{ $article->collections }}</button>

                </div>

                <div class="row row-horizon">
                    @foreach($collectors as $collector)
                        <div class="col-sm-2 col-md-2">
                            <div class="thumbnail">
                                <a href="">
                                    <img class="avatar-32" alt="{{ $collector->user->name }}" src="{{ $collector->user->avatar }}">
                                </a>
                                <div>
                                    <p class="text-center">{{ $collector->user->name }}</p>
                                    <p class="text-center small" id="confirm-collect-rel-{{ $collector->id }}">{{ trans_article_collect_status($collector->status) }}</p>
                                    <p class="text-center">
                                        <button class="btn btn-primary btn-xs comment-reply" data-toggle="tooltip" title="回复" data-source_id="{{ $article->id }}" data-to_user_id="{{ $collector->user_id }}" data-source_type="article" data-message="回复 {{ $collector->user->name }}"><i class="fa fa-comment-o"></i></button>
                                        <button class="btn btn-primary btn-xs btn-verify-collect" data-toggle="tooltip" title="审核通过" data-source_id="{{ $collector->id }}"><i class="fa fa-check-square-o"></i></button>
                                        <button class="btn btn-danger btn-xs btn-unverify-collect" data-toggle="tooltip" title="审核不通过" data-source_id="{{ $collector->id }}"><i class="fa fa-minus-square-o"></i></button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>

            <div class="widget-relation">
                <div class="row">
                    <div class="col-md-6">
                        <h4>相关活动</h4>
                        <ul class="widget-links list-unstyled">
                            @foreach($relatedArticles as $relatedArticle)
                                @if($relatedArticle->id != $article->id)
                                    <li class="widget-links-item">
                                        <a title="{{ $relatedArticle->title }}" href="{{ route('blog.article.detail',['article_id'=>$relatedArticle->id]) }}">{{ $relatedArticle->title }}</a>
                                        <small class="text-muted">{{ $relatedArticle->views }} 浏览</small>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="widget-answers mt-15">
                <h2 class="h4 post-title">{{ $article->comments }} 条评论</h2>
                @include('theme::comment.collapse',['comment_source_type'=>'article','comment_source_id'=>$article->id,'hide_cancel'=>true])
            </div>

        </div>

        <div class="col-xs-12 col-md-3 side">
            <div class="widget-user">
                <div class="media">
                    <a class="pull-left" href="{{ route('auth.space.index',['user_id'=>$article->user_id]) }}"><img class="media-object avatar-64" src="{{ $article->user->avatar }}"></a>
                    <div class="media-body ">
                        <a href="{{ route('auth.space.index',['user_id'=>$article->user_id]) }}" class="media-heading">{{ $article->user->name }}</a>
                        @if($article->user->title)
                        <p class="text-muted">{{ $article->user->title }}</p>
                        @endif
                        <p class="text-muted">{{ $article->user->userData->articles }} 篇文章</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="verify_modal" tabindex="-1"  role="dialog" aria-labelledby="change_category_modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">反馈信息</h4>
                </div>
                <div class="modal-body">
                    <form id="verify_modal_from" method="POST" action="{{ route('auth.collection.unverify') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="collect_id" name="collect_id" />
                        <div class="form-group">
                            <input type="checkbox" name="reject_enroll">不允许重新报名
                        </div>
                        <div class="form-group">
                            <textarea name="message" id="message" placeholder="写下你的反馈" class="form-control"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="verify_modal_submit">确认</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="verify_ok_modal" tabindex="-1"  role="dialog" aria-labelledby="change_category_modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">反馈信息</h4>
                </div>
                <div class="modal-body">
                    <form id="verify_ok_modal_from" method="POST" action="{{ route('auth.collection.verifyok') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="collect_ok_id" name="collect_id" />
                        <div class="form-group">
                            <textarea name="message" id="ok_message" placeholder="写下你的反馈" class="form-control"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="verify_ok_modal_submit">确认</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            var article_id = "{{ $article->id }}";
            /*评论默认展开*/
            load_comments('article',article_id);
            $("#comments-article-"+article_id).collapse('show');

            /*评论提交*/
            $(".comment-btn").click(function(){
                var source_id = $(this).data('source_id');
                var source_type = $(this).data('source_type');
                var to_user_id = $(this).data('to_user_id');
                var token = $(this).data('token');
                var content = $("#comment-"+source_type+"-content-"+source_id).val();
                add_comment(token,source_type,source_id,content,to_user_id);
                $("#comment-content-"+source_id+"").val('');
            });

            $("#verify_modal_submit").click(function(){
                var message = $("#message").val();

                if( message ){
                    $("#verify_modal_from").submit();
                }else{
                    alert("您没有输入任何内容");
                }
            });

            $("#verify_ok_modal_submit").click(function(){
                var message = $("#ok_message").val();

                if( message ){
                    $("#verify_ok_modal_from").submit();
                }else{
                    alert("您没有输入任何内容");
                }
            });

            $(".thumbnail .btn-verify-collect").on("click",function(){
                var btn_support = $(this);
                var source_id = btn_support.data('source_id');
                $('#collect_ok_id').val(source_id);
                $('#verify_ok_modal').modal('show');
            });

            $(".thumbnail .btn-unverify-collect").on("click",function(){
                var btn_support = $(this);
                var source_id = btn_support.data('source_id');
                $('#collect_id').val(source_id);
                $('#verify_modal').modal('show');
            });


        });
    </script>
@endsection