<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <title></title>
    <style type="text/css">
        body{font-family:PingFang-SC-Regular,sans-serif;margin:0}.component-card{position:relative;background:#f8f9f9;padding:53px 50px 0 50px}.component-card .title{font-family:PingFangSC-Medium,sans-serif;font-size:32px;line-height:45px}.component-card .labels{margin-top:18px}.component-card .labels .label{margin-top:5px;padding:0 23px;height:52px;line-height:52px;font-size:26px;border-radius:100px;border:1px solid #dcdcdc;display:inline-block;color:grey;margin-right:10px}.component-card .cardLine{position:relative;margin-top:31px;min-height:1px}.component-card .cardLine:after{position:absolute;right:0;bottom:0;left:0;height:2px;content:'';-webkit-transform:scaleY(.5);transform:scaleY(.5);background-color:#dcdcdc}.component-card .cardfooter{position:relative;padding-top:28px}.component-card .cardfooter .address{color:#444;font-size:26px}.component-card .cardfooter .address img{width:24px;height:29px;vertical-align:middle;margin-right:5px}.component-card .cardfooter .address span{vertical-align:middle}.component-card .cardfooter .price{position:absolute;right:0;top:42%;vertical-align:middle}.component-card .cardfooter .price .number{vertical-align:inherit;color:#66dbf9;font-size:38px;line-height:38px;font-family:PingFangSC-Medium,sans-serif}.component-card .cardfooter .price .unit{vertical-align:inherit;color:#b4b4b6;font-size:26px;line-height:26px;margin-left:6px}.component-qrcode{text-align:center;position:relative;height:213px}.component-qrcode .circle{position:absolute;left:50%;top:50%;margin-top:-106.5px;margin-left:-106.5px;width:213px;height:213px;border-radius:50%;background:#fff;overflow:hidden;z-index:999;text-align:center}.component-qrcode .qrcode{width:195.6px;height:195.6px;margin-top:8.7px;border-radius:50%}.component-qrcode .center{position:absolute;z-index:999;left:50%;width:70px;height:60px;top:50%;margin-top:-30px;margin-left:-35px}.component-qrcode-help{margin-top:30px;text-align:center;color:#fff;font-size:24px}.component-qrcode-help img{width:30px;vertical-align:middle}.component-qrcode-help span{vertical-align:middle;margin-left:8px}.component-app-footer{margin-top:25.4px;color:#fff;font-size:20px;text-align:center}.component-app-footer span{position:relative}.component-app-footer span:before{position:absolute;width:32px;left:-42px;top:14px;height:2px;content:'';-webkit-transform:scaleY(.5);transform:scaleY(.5);background-color:#dcdcdc}.component-app-footer span:after{position:absolute;width:32px;right:-42px;top:14px;height:2px;content:'';-webkit-transform:scaleY(.5);transform:scaleY(.5);background-color:#dcdcdc}.bg-card{padding:14px 0 60px;background:-webkit-linear-gradient(180deg,#8fd9ff,#4ec2fd);background:-o-linear-gradient(180deg,#8fd9ff,#4ec2fd);background:-moz-linear-gradient(180deg,#8fd9ff,#4ec2fd);background:linear-gradient(180deg,#8fd9ff,#4ec2fd);position:relative;overflow:hidden}.bg-card:before{position:absolute;content:'';width:1510px;height:1510px;left:50%;top:-1407px;margin-left:-755px;background:#f8f9f9;border-radius:50%}
    </style>
</head>
<body>
<div class="component-card">
    <div class="title">{{ $demand['title'] }}</div>
    <div class="labels">
        <div class="label">{{$demand['project_begin_time']}}开始</div><div class="label">{{$demand['project_cycle']['text']}}</div><div class="label">{{$demand['industry']['text']}}</div>
    </div>
    <div class="cardLine"></div>
    <div class="cardfooter">
        <div class="address"><img src="https://cdn.inwehub.com/weapp_demand/position@3x.png"><span>{{$demand['address']['selProvince']  === '请选择' ? '' : $demand['address']['selProvince'] }}{{$demand['address']['selCity'] ? $demand['address']['selCity'] : ''}}{{$demand['address']['selDistrict'] ? $demand['address']['selDistrict'] : ''}}</span></div>
        <div class="price"><span class="number">{{$demand['salary'].($demand['salary_upper']>$demand['salary']?'~'.$demand['salary_upper']:'')}}</span><span class="unit">元/{{$demand['salary_type'] == 0?'天':'月'}}</span></div>
    </div>
</div>
<div class="bg-card">
    <div class="component-qrcode">
        <div class="circle"><img class="qrcode" src="{{$demand['qrcodeUrl']}}"></div>
    </div>
    <div class="component-qrcode-help"><img src="https://cdn.inwehub.com/weapp_demand/zhiwen@3x.png"><span>长按识别小程序，查看项目招募信息</span></div>
    <div class="component-app-footer"><span>项目招募助手</span></div>
</div>
</body>
</html>