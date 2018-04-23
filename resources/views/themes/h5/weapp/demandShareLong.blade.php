<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <title></title>
    <style type="text/css">
        body{font-family:PingFang-SC-Regular,sans-serif;padding:69px;margin:0}.component-card{position:relative;border-radius:69px;background:#f1f1f2;padding:48px 63px}.component-card:after{position:absolute;bottom:-39px;content:'';left:50%;margin-left:-37.5px;width:0;height:0;border-style:solid;border-width:39px 39px 0;border-color:#f1f1f2 transparent transparent}.component-card .title{font-family:PingFangSC-Medium,sans-serif;font-size:69px;line-height:97.5px}.component-card .labels{margin-top:48px}.component-card .labels .label{border-radius:216px;border:3px solid #dcdcdc;display:inline-block;padding:15px 48px;margin-top:9px;color:grey}.component-card .cardLine{position:relative;margin:60px 0;min-height:3px}.component-card .cardLine:after{position:absolute;right:0;bottom:0;left:0;height:6px;content:'';-webkit-transform:scaleY(.5);transform:scaleY(.5);background-color:#dcdcdc}.component-card .cardfooter{position:relative}.component-card .cardfooter .address{color:#444;font-size:55.5px}.component-card .cardfooter .address img{width:52.5px;height:63px;vertical-align:middle;margin-right:15px}.component-card .cardfooter .address span{vertical-align:middle}.component-card .cardfooter .price{position:absolute;right:0;top:0;vertical-align:middle}.component-card .cardfooter .price .number{vertical-align:inherit;color:#66dbf9;font-size:82.5px;font-family:PingFangSC-Medium,sans-serif}.component-card .cardfooter .price .unit{vertical-align:inherit;color:#b4b4b6;font-size:55.5px;margin-left:5.1px}.component-qrcode{margin-top:60px;text-align:center;position:relative}.component-qrcode .qrcode{width:434.1px;height:434.1px}.component-qrcode .center{position:absolute;left:50%;width:112.5px;top:50%;margin-top:-48.8px;margin-left:-56.3px}.component-qrcode-help{margin-top:30px;text-align:center;color:grey;font-size:52.5px}.component-qrcode-help img{width:52.5px;vertical-align:middle}.component-qrcode-help span{vertical-align:middle;margin-left:15px}.component-app-footer{margin-top:43.2px;color:#b4b4b6;font-size:43.5px;text-align:center}.component-app-footer span{position:relative}.component-app-footer span:before{position:absolute;width:69px;left:-105px;top:27px;height:6px;content:'';-webkit-transform:scaleY(.5);transform:scaleY(.5);background-color:#dcdcdc}.component-app-footer span:after{position:absolute;width:69px;right:-105px;top:27px;height:6px;content:'';-webkit-transform:scaleY(.5);transform:scaleY(.5);background-color:#dcdcdc}
    </style>
</head>
<body>
<div class="component-card">
    <div class="title">{{ $demand['title'] }}</div>
    <div class="labels">
        <div class="label">{{$demand['project_begin_time']}}开始</div>
        <div class="label">{{$demand['project_cycle']['text']}}</div>
        <div class="label">{{$demand['industry']['text']}}</div>
    </div>
    <div class="cardLine"></div>
    <div class="cardfooter">
        <div class="address"><img src="https://cdn.inwehub.com/weapp_demand/position@3x.png"><span>{{$demand['address']['selProvince']  === '请选择' ? '' : $demand['address']['selProvince'] }}{{$demand['address']['selCity'] ? $demand['address']['selCity'] : ''}}{{$demand['address']['selDistrict'] ? $demand['address']['selDistrict'] : ''}}</span></div>
        <div class="price"><span class="number">{{$demand['salary']}}</span><span class="unit">元/{{$demand['salary_type'] == 0?'天':'月'}}</span></div>
    </div>
    <div class="cardTriangle"></div>
</div>
<div class="component-qrcode"><img class="qrcode" src="{{$demand['qrcodeUrl']}}"></div>
<div class="component-qrcode-help"><img src="https://cdn.inwehub.com/weapp_demand/zhiwen@3x.png"><span>长按识别小程序，查看项目招募信息</span></div>
<div class="component-app-footer"><span>项目招募助手</span></div>
</body>
</html>