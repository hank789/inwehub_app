<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>悬赏问答</title>
    <style>
    </style>
</head>
<body style="margin: 0;padding: 0; width:1125px;background:rgba(3,174,249,1);font-family:PingFangSC-Regular; padding-bottom: 48px;">
<!-- 悬赏金额 -->
<div>
    <div style="width:184px;height:150px;background-image: url(https://cdn.inwehub.com/questions/2018/05/topPrice@3x.png);float: right;margin-right: 49px; text-align: center; font-size:39px;color:rgba(35,82,128,1);line-height:48px;">
        <p style="margin-top: 20px;"><span style="font-size: 48px; color: #235280">{{ $data['price'] }}</span>元<br/>悬赏金额</p>
    </div>
    <div style="clear: both;"></div>
</div>
<!-- userinfor -->
<div style="text-align: center;margin-top: -35px;">
    <div style="width:198px;height:198px;border: 4px solid rgba(168,223,247,1);border-radius: 50%;text-align: center;margin: 0 auto; background: url({{ $data['user_avatar'] }});background-size: cover;background-repeat: no-repeat;"></div>
    <div style="font-size:51px;color:#fff; margin-bottom: 96px;"><p style="line-height:30px;"><span>{{ $data['username'] }} </span>邀您回答悬赏问题</p></div>
    <div style="width:90px;height:3px;background: rgba(168,223,247,1);margin: 0 auto;"></div>
    <p style="font-size:36px;color:rgba(255,255,255,1);line-height:50px;">来自 <span>{{ $data['question_username'] }}</span> 的提问</p>
</div>
<!-- text -->
<div style="padding: 0 47px 0 50px; ">
    <div style="padding:30px 66px 90px 63px;background: #fff;border-radius: 12px; width: 900px; margin: 0 auto; font-size: 42px;color:#808080;">
        <div style="white-space:pre-wrap;word-wrap:break-word">{{$data['question_title']}}</div>
        <div style="font-size: 36px; margin-top: 90px;">
            @foreach($data['tags'] as $tag)
                <span style="height: 63px; line-height: 63px; display: inline-block;padding: 0 24px; text-align: center;border:1px solid rgba(219,219,219,1);border-radius: 150px; ">{{ $tag['name'] }}</span>
            @endforeach
        </div>
    </div>
</div>
<!-- footer -->
<div style="padding: 0 47px 0 50px; margin-top: 40px; ">
    <div style="padding:36px 66px 36px 63px;background: #fff;border-radius: 12px; width: 900px; margin: 0 auto;font-size: 42px;color:rgba(128,128,128,1);">
        <div>
            <div style="float: right; width: 198px; height: 198px;" id="qrcodeCanvas"></div>
            <div style="float: right; text-align: right; margin-right: 27px; margin-top: 27px;">
                <img src="http://cdn.inwehub.com/questions/2018/05/logo@3x.png" alt="">
                <p style="margin: 0;">长按识别二维码参与回答</p>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
</div>
<script src="{{ asset('/static/js/jquery.min.js') }}"></script>
<script src="{{ asset('/static/js/jquery.qrcode.min.js') }}"></script>
<script>
    jQuery('#qrcodeCanvas').qrcode({
        width   : 198,
        height  : 198,
        text	: "{{ $data['qrcode'] }}"
    });
</script>
</body>
</html>