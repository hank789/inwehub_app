@extends('theme::layout.public')

@section('seo_title')动态@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-md-9 main">
            <div class="widget-streams messages mt-15">
                    @foreach($messages as $message)
                    <section class="hover-show streams-item" id="message_{{ $message->id }}">
                        <div class="stream-wrap media">
                            <div class="pull-left">
                                <img class="media-object avatar-40" src="{{ $message->user->avatar }}" alt="{{ $message->user->name }}">
                            </div>
                            <div class="media-body">
                                <a target="_blank" href="{{ route('auth.space.index',['id'=>$message->user_id]) }}"> {{ $message->user->name }}</a> {{ $message->data['feed_content'] }}:
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

                                </div>
                                <div class="meta mt-10">
                                    <span class="text-muted">{{ $message->created_at }} </span>
                                <span class="text-muted">
                                    <a href="javascript:void(0)" onclick="top_message({{ $message->id }})">{{ $message->top > 0 ? '取消置顶('.$message->top.')':'置顶' }}</a>
                                    <a href="javascript:void(0)" onclick="delete_message({{ $message->id }})">删除</a>
                                </span>
                                </div>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
            <div class="text-center">
                {!! str_replace('/?', '?', $messages->render()) !!}
            </div>
        </div>
    </div>
@endsection

@section('script')
<script type="text/javascript">
    function delete_message(message_id)
    {
        if(!confirm('确认删除该信息？')){
            return false;
        }

        $.get('/manager/feed/destroy/'+message_id,function(msg){
            if(msg === 'ok'){
                $("#message_"+message_id).remove();
            }else{
                alert('操作失败，请稍后再试！');
            }
        });
    }
    function top_message(message_id)
    {
        var sort = prompt('输入置顶排序(值越大越靠前，输入0则取消排序)',1);

        $.get('/manager/feed/top/'+message_id+'/'+sort,function(msg){
            if(msg === 'ok'){
                alert('置顶成功！');
            }else{
                alert('操作失败，请稍后再试！');
            }
        });

    }
</script>
@endsection