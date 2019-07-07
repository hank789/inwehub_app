@extends('emails.layout')

@section('title')Inwehub·今日热点推荐@endsection

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="content-block">
                <div style="width: 576px; height: 100%; padding: 20px 30px 34px; ">
                    <div style="position: relative; height: 26px;">
                        <div style="width: 231px; height: 24px; position: absolute; left: 0px;"><img style="width: 100%; height: 100%;" src="https://cdn.inwehub.com/system/topLog@3x.png" alt=""></div>
                        <div style="position: absolute; right: 0px;"><span style="font-family:PingFangSC-Medium;font-weight:500;color: #4A5F7B; font-size: 18px;">{{ date('m月d日',strtotime($date)) }}</span><span style="font-size: 14px; color: #7C93B2; margin-left: 2px;">{{ $weekday }}</span></div>
                    </div>

                    <div style="width: 100%;height: 2px; background: #03AEF9; margin-top: 21px;"></div>

                    <div class="" style="margin-top: 10px;">
                        @foreach($items as $key=>$item)
                            <div style="position: relative; height: 111px; margin: 20px 0; ">
                                <div style="position: absolute; left: 0; height: 90px; width: 100%;">
                                    <a target="_blank" href="{{ $item['link_url'] }}" style="text-decoration:none; color: #4A5F7B; font-size: 18px; font-family:PingFangSC-Medium; font-weight:500; line-height:28px; {{ $item['img']?'width: 350px;':'' }} position: absolute; top: 0; display:-webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden;">{{ $key+1 }}. {{ $item['title'] }}</a>
                                    
                                </div>
                                @if ($item['img'])
                                    <div style="position: absolute; right: 0; width: 132px; height: 89px; border-radius: 4px;"><img style="width: 132px; height: 89px; border-radius: 4px;" src="{{ $item['img'] }}" alt=""></div>
                                @endif
                                @if (($key+1) < count($items))
                                    <div style="position: absolute; bottom: 0px; width: 100%; height: 2px; background: #E0E8EF;"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div style="color: #4A5F7B;font-size: 18px; font-family:PingFangSC-Medium; font-weight:500; margin-top: 71px;">看邮件不方便？您可试试</div>

                    <div style="width: 320px; height: 150px; background: #F7FBFE; position: relative; padding-top: 20px; margin-top: 29px;">
                        <div style="width: 99px; display: inline-block; margin: 0 30px;">
                            <img style="width: 99px;height: 99px;" src="https://cdn.inwehub.com/system/appstore.png" alt="">
                            <div style="text-align: center; color: #576F8F; font-size: 14px; margin-top: 10px;">APP</div>
                        </div>

                        <div style=" height: 125px; border-left: 1px dashed #E0E8EF; position: absolute; top: 23px; left: 159px;"></div>

                        <div style="width: 99px; display: inline-block; margin: 0 28px;">
                            <img style="width: 99px;height: 99px;" src="https://cdn.inwehub.com/system/gzh@3x.png" alt="">
                            <div style="text-align: center; color: #576F8F; font-size: 14px; margin-top: 10px;">微信服务号</div>
                        </div>
                    </div>

                    <div style="margin-top: 50px;">
                        <div style="color: #7C93B2; font-size: 14px; margin-top: 10px; margin-left: 10px">
                            <span style="display: inline-block; width: 4px; height: 4px; background: #03AEF9; border-radius: 50%; position: relative; top: -3px; right: 10px;"></span>本邮件为系统自动发送邮件，请勿直接回复
                        </div>
                        <div style="color: #7C93B2; font-size: 14px; margin-top: 10px; margin-left: 10px">
                            <span style="display: inline-block; width: 4px; height: 4px; background: #03AEF9; border-radius: 50%; position: relative; top: -3px; right: 10px;"></span>如果您不想收到此邮件，点击<a target="_blank" href="{{ route('website.unsubscribeEmail',['uid'=>$uid]) }}?redirect=%%user_defined_unsubscribe_link%%" style="color: #4A5F7B;">退订</a>
                        </div>
                        <div style="color: #7C93B2; font-size: 14px; margin-top: 10px; margin-left: 10px">
                            <span style="display: inline-block; width: 4px; height: 4px; background: #03AEF9; border-radius: 50%; position: relative; top: -3px; right: 10px;"></span>联系我们：邮箱hi@inwehub.com，微信hiinwe
                        </div>
                    </div>

                </div>
            </td>
        </tr>
    </table>
@endsection