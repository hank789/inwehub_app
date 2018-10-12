@extends('theme::layout.public')

@section('seo_title'){{ parse_seo_template('seo_topic_title',$tag) }}@endsection
@section('seo_description'){{ parse_seo_template('seo_topic_description',$tag) }}@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-md-9 main">

            <section class="tag-header mt-20">
                <div>
                    @if($tag->logo)
                    <img class="pull-left avatar-27 mr-10" src="{{ $tag->logo }}">
                    @endif
                    <span class="h4 tag-header-title">{{ $tag->name }}</span>

                    <div class="tag-header-follow">
                        @if(Auth()->check() && Auth()->user()->isFollowed(get_class($tag),$tag->id))
                            <button type="button" id="follow-button" class="btn btn-default btn-xs active" data-source_type = "tag" data-source_id = "{{ $tag->id }}"  data-show_num="false"  data-toggle="tooltip" data-placement="right" title="" data-original-title="关注后将获得更新提醒">已关注</button>
                        @else
                            <button type="button" id="follow-button" class="btn btn-default btn-xs" data-source_type = "tag" data-source_id = "{{ $tag->id }}"  data-show_num="false" data-toggle="tooltip" data-placement="right" title="" data-original-title="关注后将获得更新提醒">关注</button>
                        @endif
                            <a class="btn btn-default btn-xs" href="{{ route('admin.tag.edit',['id'=>$tag->id]) }}" data-toggle="tooltip" title="编辑"><i class="fa fa-edit"></i></a>
                            <button class="btn btn-default btn-xs" data-toggle="tooltip" title="删除选中项" onclick="deleteTag('{{ $tag->id }}')"><i class="fa fa-trash-o"></i></button>

                    </div>
                </div>
                @if($tag->summary)
                <p class="tag-header-summary">{{ $tag->summary }}...<a href="{{ route('ask.tag.index',['id'=>$tag->id,'source_type'=>'details']) }}">[ 百科 ]</a></p>
                @else
                <p class="tag-header-summary">暂无介绍</p>
                @endif
            </section>

            <ul class="nav nav-tabs nav-tabs-zen">
                <li @if($source_type==='questions') class="active" @endif ><a href="{{ route('ask.tag.index',['id'=>$tag->id]) }}">问题</a></li>
                <li @if($source_type==='users') class="active" @endif ><a href="{{ route('ask.tag.index',['id'=>$tag->id,'source_type'=>'users']) }}">用户标签</a></li>
                <li @if($source_type==='submissions') class="active" @endif ><a href="{{ route('ask.tag.index',['id'=>$tag->id,'source_type'=>'submissions']) }}">分享</a></li>
                <li @if($source_type==='userJobs') class="active" @endif ><a href="{{ route('ask.tag.index',['id'=>$tag->id,'source_type'=>'userJobs']) }}">工作经历</a></li>
                <li @if($source_type==='userProjects') class="active" @endif ><a href="{{ route('ask.tag.index',['id'=>$tag->id,'source_type'=>'userProjects']) }}">项目经历</a></li>
            </ul>
            <div class="tab-content">
                <div class="stream-list">
                    @if($source_type==='questions')
                        @foreach($sources as $question)
                            <section class="stream-list-item">
                                <div class="qa-rank">
                                    <div class="answers @if($question->status===2) solved @elseif($question->answers>0) answered @endif ">
                                        {{ $question->answers }}<small>回答</small>
                                    </div>
                                    <div class="views hidden-xs">
                                        {{ $question->views }}<small>浏览</small>
                                    </div>
                                </div>
                                <div class="summary">
                                    <ul class="author list-inline">
                                        <li>
                                            <a href="{{ route('auth.space.index',['user_id'=>$question->user->id]) }}">{{ $question->user->name }}</a>
                                            <span class="split"></span>
                                            <span class="askDate">{{ $question->created_at }}</span>
                                        </li>
                                    </ul>
                                    <h2 class="title"><a href="{{ route('ask.question.detail',['id'=>$question->id]) }}">{{ $question->title }}</a></h2>
                                    @if($question->tags)
                                        <ul class="taglist-inline ib">
                                            @foreach($question->tags as $tag)
                                                <li class="tagPopup"><a class="tag" href="{{ route('ask.tag.index',['id'=>$tag->id]) }}">{{ $tag->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </section>
                        @endforeach
                    @elseif($source_type==='users')
                        @foreach($sources as $userTag)
                            <section class="stream-list-item">
                                <div class="summary">
                                    <a href="{{ route('auth.space.index',['user_id'=>$userTag->user->user_id]) }}" class="user-card pull-left" target="_blank">
                                        <img class="avatar-50"  src="{{ $userTag->user->avatar }}" alt="{{ $userTag->user->name }}"></a>
                                    </a>
                                    <strong><a href="{{ route('auth.space.index',['user_id'=>$userTag->user->user_id]) }}" target="_blank">{{ $userTag->user->name }}</a></strong>
                                </div>

                                    <ul class="taglist-inline ib">
                                        @foreach($userTag->user->userTags as $tagInfo)
                                            @if ($tagInfo->tag)
                                                <li class="tagPopup"><a class="tag" href="{{ route('ask.tag.index',['id'=>$tagInfo->tag->id]) }}">{{ $tagInfo->tag->name }}</a></li>
                                            @endif
                                        @endforeach
                                    </ul>
                            </section>
                        @endforeach
                    @elseif($source_type==='submissions')
                        @foreach($sources as $submission)
                            <section class="stream-list-item">

                                <div class="summary">
                                    <h2 class="title">{!! $submission->title !!}</h2>
                                    <ul class="author list-inline">
                                        <li class="pull-right" title="{{ $submission->collections }} 收藏">
                                            <small class="glyphicon glyphicon-bookmark"></small> {{ $submission->collections }}
                                        </li>
                                        <li>
                                            <a href="{{ route('auth.space.index',['user_id'=>$submission->user_id]) }}">
                                                <img class="avatar-20 mr-10 hidden-xs" src="{{ $submission->user->avatar }}" alt="{{ $submission->user->name }}"> {{ $submission->user->name }}
                                            </a>
                                            发布于 {{ timestamp_format($submission->created_at) }}
                                        </li>
                                    </ul>
                                    @if($submission->tags)
                                        <ul class="taglist-inline ib">
                                            @foreach($submission->tags as $tag)
                                                <li class="tagPopup"><a class="tag" href="{{ route('ask.tag.index',['id'=>$tag->id]) }}">{{ $tag->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </section>
                        @endforeach
                    @elseif($source_type==='userJobs')
                        @foreach($sources as $userJob)
                            <section class="stream-list-item">

                                <div class="summary">
                                    <ul class="author list-inline">
                                        <li>
                                            <a href="{{ route('auth.space.index',['user_id'=>$userJob->user->id]) }}">{{ $userJob->user->name }}</a>
                                        </li>
                                    </ul>
                                    <h2 class="title">{{ $userJob->company }}</h2>
                                    <p class="excerpt wordbreak hidden-xs">{{ $userJob->title }}</p>
                                    <p class="excerpt wordbreak hidden-xs">{{ $userJob->description }}</p>
                                @if($userJob->tags)
                                        <ul class="taglist-inline ib">
                                            @foreach($userJob->tags as $tag)
                                                <li class="tagPopup"><a class="tag" href="{{ route('ask.tag.index',['id'=>$tag->id]) }}">{{ $tag->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </section>
                        @endforeach
                    @elseif($source_type==='userProjects')
                        @foreach($sources as $userProject)
                            <section class="stream-list-item">

                                <div class="summary">
                                    <ul class="author list-inline">
                                        <li>
                                            <a href="{{ route('auth.space.index',['user_id'=>$userProject->user->id]) }}">{{ $userProject->user->name }}</a>
                                        </li>
                                    </ul>
                                    <h2 class="title">{{ $userProject->project_name }}</h2>
                                    <p class="excerpt wordbreak hidden-xs">{{ $userProject->title }}</p>
                                    <p class="excerpt wordbreak hidden-xs">{{ $userProject->customer_name }}</p>
                                    <p class="excerpt wordbreak hidden-xs">{{ $userProject->description }}</p>
                                    @if($userProject->tags)
                                        <ul class="taglist-inline ib">
                                            @foreach($userProject->tags as $tag)
                                                <li class="tagPopup"><a class="tag" href="{{ route('ask.tag.index',['id'=>$tag->id]) }}">{{ $tag->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </section>
                        @endforeach
                    @else
                        <div class="text-fmt mt-10">{!! $tag->description  !!}</div>
                    @endif



                </div>

                @if($source_type!=='details')
                <div class="text-center">
                    {!! str_replace('/?', '?', $sources->render()) !!}
                </div>
                @endif
            </div>
        </div>

        <div class="col-xs-12 col-md-3 side">
            <div class="widget-box">
                <h2 class="h4 widget-box__title">相关标签</h2>
                <ul class="taglist-inline multi">
                    @foreach($tag->relations() as $relationTag)
                        <li class="tagPopup"><a class="tag" href="{{ route('ask.tag.index',['id'=>$relationTag->id]) }}">{{ $relationTag->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="widget-box">
                <h2 class="h4 widget-box-title">推荐用户</h2>
                <ul class="list-unstyled">
                    @foreach($followers as $follower)
                        <li class="media  widget-user-item ">
                            <a href="{{ route('auth.space.index',['user_id'=>$follower->user_id]) }}" class="user-card pull-left" target="_blank">
                                <img class="avatar-50"  src="{{ get_user_avatar($follower->user_id) }}" alt="{{ $follower->user->name }}"></a>
                            </a>
                            <div class="media-object">
                                <strong><a href="{{ route('auth.space.index',['user_id'=>$follower->user_id]) }}" target="_blank">{{ $follower->user->name }}</a></strong>
                                <p class="text-muted"> {{ $follower->answers }} 回答，{{ $follower->supports }}赞同</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div><!-- /.side -->
    </div>
@endsection