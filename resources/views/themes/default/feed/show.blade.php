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
                                    @foreach($message->getSourceFeedData()['feed'] as $field=>$value)
                                        {{ $field }} : {{ $value }}<br>
                                    @endforeach
                                </div>
                                <div class="meta mt-10">
                                    <span class="text-muted">{{ timestamp_format($message->created_at) }} </span>
                                <span class="pull-right">
                                    <a href="javascript:void(0)" class="text-muted" onclick="delete_message({{ $message->id }})">删除</a>
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

        $.get('/feed/destroy/'+message_id,function(msg){
            if(msg === 'ok'){
                $("#message_"+message_id).remove();
            }else{
                alert('操作失败，请稍后再试！');
            }
        });

    }
</script>
@endsection