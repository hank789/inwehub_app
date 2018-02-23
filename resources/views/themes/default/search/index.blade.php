@extends('theme::layout.public')

@section('seo_title')搜索 - 第{{ $list->currentPage() }}页 - {{ Setting()->get('website_name') }}@endsection

@section('content')
        <div class="container mt-20">
            <div class="row">
                <div class="container">
                    <ul class="search-category nav nav-pills">
                        <li @if($filter==='questions') class="active" @endif ><a href="{{ route('auth.search.index',['filter'=>'questions']) }}?query={{ $word }}">问答</a></li>
                        <li @if($filter==='feeds') class="active" @endif ><a href="{{ route('auth.search.index',['filter'=>'feeds']) }}?query={{ $word }}">动态流</a></li>
                        <li @if($filter==='articles') class="active" @endif><a href="{{ route('auth.search.index',['filter'=>'articles']) }}?query={{ $word }}">文章</a></li>
                        <li @if($filter==='tags') class="active" @endif><a href="{{ route('auth.search.index',['filter'=>'tags']) }}?query={{ $word }}">标签</a></li>
                        <li @if($filter==='users') class="active" @endif><a href="{{ route('auth.search.index',['filter'=>'users']) }}?query={{ $word }}">用户</a></li>
                    </ul>
                    <form action="{{ route('auth.search.index') }}" class="row" method="GET">
                        <div class="col-md-9">
                            <input class="input-lg form-control" type="text" name="query" value="{{ $word }}" placeholder="输入关键字搜索">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">搜索</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9 main search-result">
                    <h3 class="h5 mt0">找到约 <strong>{{ $list->count() }}</strong> 条结果</h3>
                    @if($filter==='questions')
                        @foreach($list as $question)
                            <section class="widget-question">
                                <h2 class="h4">
                                    @if($question->status==2)
                                        <span class="label label-success pull-left mr-5">解决</span>
                                    @endif
                                    <a href="{{ route('ask.question.detail',['question_id'=>$question->id]) }}" target="_blank">{{ $question->title }}</a>
                                </h2>
                                <p class="excerpt">{{ str_limit(strip_tags($question->description),200) }}</p>
                            </section>
                        @endforeach
                    @elseif($filter==='articles')
                        @foreach($list as $article)
                            <section class="widget-blog">
                                <h2 class="h4">
                                    <a href="{{ route('blog.article.detail',['article_id'=>$article->id]) }}" target="_blank">{{ $article->title }}</a>
                                </h2>
                                <p class="excerpt">{{ str_limit(strip_tags($article->summary),200) }}</p>
                            </section>
                        @endforeach
                    @elseif($filter==='users')
                        @foreach($list as $user)
                            <section class="widget-member">
                                <h2 class="h4">
                                    <a href="{{ route('auth.space.index',['user_id'=>$user->id]) }}" target="_blank">{{ $user->name }}</a>
                                    @if($user->title) <span class="text-muted"> - {{ $user->title }}</span> @endif
                                </h2>
                                <p class="excerpt">{{ str_limit(strip_tags($user->description),200) }}</p>
                            </section>
                        @endforeach
                    @elseif($filter==='tags')
                        @foreach($list as $tag)
                            <section class="widget-tag">
                                <h2 class="h4">
                                    <a href="{{ route('ask.tag.index',['name'=>$tag->name]) }}" target="_blank">{{ $tag->name }}</a>
                                </h2>
                                <p class="excerpt">{{ str_limit(strip_tags($tag->description),200) }}</p>
                            </section>
                        @endforeach
                    @elseif($filter==='feeds')
                        @foreach($list as $message)
                            <section class="widget-tag">
                                <h2 class="h4">
                                    <img class="media-object avatar-40" src="{{ $message->user->avatar }}" alt="{{ $message->user->name }}">
                                    <a target="_blank" href="{{ route('auth.space.index',['id'=>$message->user_id]) }}"> {{ $message->user->name }}</a> {{ $message->data['feed_content'] }}:
                                </h2>
                                <div class="full-text fmt">
                                    feed_id : {{ $message->id }}<br>
                                    source_id : {{ $message->source_id }}<br>
                                    source_type : {{ $message->source_type }}<br>
                                    feed_type : {{ $message->feed_type }}<br>
                                    tags : {{ $message->tags }}<br>
                                    @if ($feedData = $message->getSourceFeedData())
                                        @foreach($feedData['feed'] as $field=>$value)
                                            @if (is_array($value))
                                                @foreach($value as $f2=>$v2)
                                                    {{ $f2 }} : {{ is_array($v2) ? json_encode($v2,JSON_UNESCAPED_UNICODE) : $v2 }}<br>
                                                @endforeach
                                            @else
                                                {{ $field }} : {{ $value }}<br>
                                            @endif
                                        @endforeach
                                    @endif
                                    <span class="text-muted">{{ $message->created_at }} </span>
                                </div>
                            </section>
                        @endforeach

                    @endif
                    <div class="text-center">
                        {!! str_replace('/?', '?', $list->render()) !!}
                    </div>
                </div>
                <div class="col-md-3 side">
                    <ul class="list-unstyled">
                    </ul>
                </div>
            </div>
        </div>
@endsection
