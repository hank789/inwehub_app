<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <title></title>
    <style type="text/css">
        body{font-family:PingFang-SC-Regular,sans-serif;margin:0;line-height:31.5px}*{-webkit-box-sizing:border-box;box-sizing:border-box;outline:0}.component-card{position:relative;background:#f8f9f9;padding:79.5px 75px 0 75px}.component-card .title{font-family:PingFangSC-Medium,sans-serif;font-size:48px;line-height:67.5px}.component-card .labels{margin-top:27px}.component-card .labels .label{margin-top:7.5px;padding:0 34.5px;height:78px;line-height:78px;font-size:39px;border-radius:150px;border:1.5px solid #dcdcdc;display:inline-block;color:grey;margin-right:15px}.component-card .cardLine{position:relative;margin-top:46.5px;min-height:1.5px}.component-card .cardLine:after{position:absolute;right:0;bottom:0;left:0;height:3px;content:'';-webkit-transform:scaleY(.5);transform:scaleY(.5);background-color:#dcdcdc}.component-card .cardfooter{position:relative;padding-top:42px}.component-card .cardfooter .address{color:#444;font-size:39px}.component-card .cardfooter .address img{width:36px;height:43.5px;vertical-align:middle;margin-right:7.5px}.component-card .cardfooter .address span{vertical-align:middle}.component-card .cardfooter .price{position:absolute;right:0;top:42%;vertical-align:middle}.component-card .cardfooter .price .number{vertical-align:inherit;color:#66dbf9;font-size:57px;line-height:57px;font-family:PingFangSC-Medium,sans-serif}.component-card .cardfooter .price .unit{vertical-align:inherit;color:#b4b4b6;font-size:39px;line-height:39px;margin-left:9px}.component-qrcode{text-align:center;position:relative;height:319.5px}.component-qrcode .circle{position:absolute;left:50%;top:50%;margin-top:-159.8px;margin-left:-159.8px;width:319.5px;height:319.5px;border-radius:50%;background:#fff;overflow:hidden;z-index:999;text-align:center}.component-qrcode .qrcode{width:293.4px;height:293.4px;margin-top:13.1px;border-radius:50%}.component-qrcode .center{position:absolute;z-index:999;left:50%;width:105px;height:90px;top:50%;margin-top:-45px;margin-left:-52.5px}.component-qrcode-help{margin-top:45px;text-align:center;color:#fff;font-size:36px}.component-qrcode-help img{width:45px;vertical-align:middle}.component-qrcode-help span{vertical-align:middle;margin-left:12px}.component-app-footer{margin-top:39.6px;color:#fff;font-size:30px;text-align:center}.component-app-footer span{position:relative}.component-app-footer span:before{position:absolute;width:48px;left:-63px;top:19.5px;height:3px;content:'';-webkit-transform:scaleY(.5);transform:scaleY(.5);background-color:#dcdcdc}.component-app-footer span:after{position:absolute;width:48px;right:-63px;top:19.5px;height:3px;content:'';-webkit-transform:scaleY(.5);transform:scaleY(.5);background-color:#dcdcdc}.bg-card{padding:21px 0 90px;background:-webkit-linear-gradient(180deg,#8fd9ff,#4ec2fd);background:-o-linear-gradient(180deg,#8fd9ff,#4ec2fd);background:-moz-linear-gradient(180deg,#8fd9ff,#4ec2fd);background:linear-gradient(180deg,#8fd9ff,#4ec2fd);position:relative;overflow:hidden}.bg-card:before{position:absolute;content:'';width:2265px;height:2265px;left:50%;bottom:435.8px;margin-left:-1132.5px;background:#f8f9f9;border-radius:50%}
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