@extends('theme::layout.public')

@section('seo_title')发私信给{{ $toUser->name }} - {{ Setting()->get('website_name') }}@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-md-9 main">
            <div class="mt-30 text-muted">
                <span><a  href="{{ route('auth.space.index',['id'=>$fromUser->id]) }}">{{ $fromUser->name }}</a> 发私信给 <a href="{{ route('auth.space.index',['id'=>$toUser->id]) }}">{{ $toUser->name }}</a> ： </span>
            </div>
            <div class="mt-15 clearfix">
                <form id="messageForm" method="POST" role="form" action="{{ route('auth.message.store') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="to_user_id" value="{{ $toUser->id }}" />
                    <input type="hidden" name="from_user_id" value="{{ $fromUser->id }}" />
                    <input type="hidden" name="room_id" value="{{ $room_id }}" />
                    <div class="form-group">
                        <textarea name="text" id="message_content" placeholder="请输入私信内容" class="form-control" style="height:100px;"></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary pull-right">发&nbsp;&nbsp;送</button>
                    </div>
                </form>
            </div>

            <div class="widget-streams messages mt-15">
                    @foreach($messages as $message)
                    @php
                        $message->data = json_decode($message->data,true)
                    @endphp
                    <section class="hover-show streams-item" id="message_{{ $message->id }}">
                        <div class="stream-wrap media">
                            <div class="pull-left">
                                <a href="{{ route('auth.space.index',['id'=>$message->user_id]) }}" target="_blank">
                                    <img class="media-object avatar-40" src="{{ $message->user->avatar }}" alt="{{ $message->user->name }}">
                                </a>
                            </div>
                            <div class="media-body">
                                <a target="_blank" href="{{ route('auth.space.index',['id'=>$message->user_id]) }}"> {{ $message->user->name }}</a> :
                                <div class="full-text fmt">
                                    {{ $message->data['text'] }}
                                    @if (isset($message->data['img']))
                                        <img class="media-object" src="{{ $message->data['img'] }}" alt="{{ $message->user->name }}">
                                    @endif
                                </div>
                                <div class="meta mt-10">
                                    <span class="text-muted">{{ timestamp_format($message->created_at) }} </span>
                                <span class="pull-right" style="display: none;">
                                    <a href="javascript:void(0)" class="text-muted" onclick="delete_message({{ $message->id }})">删除</a>
                                </span>
                                </div>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
            <div class="text-center">
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

        $.get('/message/destroy/'+message_id,function(msg){
            if(msg === 'ok'){
                $("#message_"+message_id).remove();
            }else{
                alert('操作失败，请稍后再试！');
            }
        });

    }
</script>
@endsection